<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminBusinessController;
use App\Http\Controllers\ProfileController;
use App\Models\Business;
use App\Models\BusinessOrder;
use Illuminate\Database\Schema\Blueprint;
use App\Models\BusinessReservation;
use App\Models\BusinessMemberHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('welcome')->with('open_login', true);
})->name('login');

Route::get('/signup', function () {
    return view('welcome')->with('open_signup', true);
})->name('signup');

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/signup', [AuthController::class, 'signup'])->name('auth.signup');
Route::post('/business/create', [AuthController::class, 'createBusiness'])->name('auth.business.create');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');

    Route::get('/users/main', function () {
        if (Auth::user()?->role !== 'user') {
            abort(403);
        }

        return view('users.main');
    })->name('users.main');

    Route::get('/users/nearest-gym', function () {
        if (Auth::user()?->role !== 'user') {
            abort(403);
        }

        $gymLocations = Business::query()
            ->whereHas('user', function ($query) {
                $query->where('role', 'admin');
            })
            ->get(['id', 'business_name', 'latitude', 'longitude']);

        return view('users.nearest-gym', [
            'gymLocations' => $gymLocations,
        ]);
    })->name('users.nearest-gym');

    Route::get('/users/gym/{business}', function (Business $business) {
        if (Auth::user()?->role !== 'user') {
            abort(403);
        }

        if (!Schema::hasColumn('businesses', 'status')) {
            Schema::table('businesses', function (Blueprint $table) {
                $table->string('status', 20)->default('open')->after('business_name');
            });
        }

        DB::statement("UPDATE businesses SET status = CASE WHEN notes LIKE '%[[GYM_MANUAL_CLOSED]]%' THEN 'closed' ELSE 'open' END WHERE status IS NULL OR status = ''");

        $business->load([
            'user:id,role,location,business_name',
            'products' => function ($query) {
                $query->latest();
            },
            'subscriptions' => function ($query) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now())
                    ->latest();
            },
        ]);

        if ($business->user?->role !== 'admin') {
            abort(404);
        }

        $userReservation = BusinessReservation::query()
            ->where('business_id', $business->id)
            ->where('user_id', Auth::id())
            ->first();

        $hasPendingStorePayment = BusinessOrder::query()
            ->where('business_id', $business->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('product_id')
            ->where('payment_status', 'pay_at_gym')
            ->exists();

        $userSubscriptionOrders = BusinessOrder::query()
            ->with('subscription:id,duration,subscription_type,expires_at')
            ->where('business_id', $business->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('subscription_id')
            ->whereIn('payment_status', ['pending', 'paid'])
            ->latest('ordered_at')
            ->latest('id')
            ->get();

        $latestUserSubscriptionOrder = $userSubscriptionOrders->first();
        $hasSubscriptionAccess = $userSubscriptionOrders->isNotEmpty();

        $hasReserved = $userReservation !== null;
        return view('users.gym-store', [
            'business' => $business,
            'products' => $business->products,
            'subscriptions' => $business->subscriptions,
            'reservationCount' => BusinessReservation::query()->where('business_id', $business->id)->count(),
            'hasReserved' => $hasReserved,
            'hasPendingStorePayment' => $hasPendingStorePayment,
            'userSubscriptionOrders' => $userSubscriptionOrders,
            'latestUserSubscriptionOrder' => $latestUserSubscriptionOrder,
            'hasSubscriptionAccess' => $hasSubscriptionAccess,
        ]);
    })->name('users.gym.view');

    Route::post('/users/gym/{business}/reserve', [AdminBusinessController::class, 'reserveGym'])->name('users.gym.reserve');
    Route::delete('/users/gym/{business}/reserve', [AdminBusinessController::class, 'cancelGymReservation'])->name('users.gym.reserve.cancel');
    Route::post('/users/gym/{business}/products/{product}/order', [AdminBusinessController::class, 'placeOnlineOrder'])->name('users.gym.products.order');
    Route::post('/users/gym/{business}/subscriptions/{subscription}/buy', [AdminBusinessController::class, 'placeSubscriptionOrder'])->name('users.gym.subscriptions.buy');

    Route::get('/users/gym/{business}/store', function (Business $business) {
        return redirect()->route('users.gym.view', $business);
    })->name('users.gym.store');

    Route::get('/users/gym/{business}/products/{product}/media', [AdminBusinessController::class, 'userProductMedia'])->name('users.gym.products.media');
    Route::get('/users/gym/{business}/media/{type}', [AdminBusinessController::class, 'userMedia'])->name('users.gym.media');

    Route::get('/users/profile', [ProfileController::class, 'edit'])->name('users.profile.edit');
    Route::put('/users/profile', [ProfileController::class, 'update'])->name('users.profile.update');

    Route::get('/admin/main', function () {
        if (Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $business = Business::where('user_id', Auth::id())->first();

        return view('admin.main', [
            'business' => $business,
        ]);
    })->name('admin.main');

    Route::get('/admin/business', function () {
        if (Auth::user()?->role !== 'admin') {
            abort(403);
        }

        $business = Business::where('user_id', Auth::id())->first();

        return view('admin.business', [
            'business' => $business,
        ]);
    })->name('admin.business');

    Route::post('/admin/business', [AdminBusinessController::class, 'store'])->name('admin.business.store');
    Route::get('/admin/business/{business}/settings', [AdminBusinessController::class, 'settings'])->name('admin.business.settings');
    Route::get('/admin/business/{business}/media/{type}', [AdminBusinessController::class, 'media'])->name('admin.business.media');
    Route::put('/admin/business/{business}/settings', [AdminBusinessController::class, 'updateSettings'])->name('admin.business.settings.update');
    Route::patch('/admin/business/{business}/status', [AdminBusinessController::class, 'toggleGymStatus'])->name('admin.business.status.toggle');
    Route::post('/admin/business/{business}/products', [AdminBusinessController::class, 'storeProduct'])->name('admin.business.products.store');
    Route::patch('/admin/business/{business}/products/{product}', [AdminBusinessController::class, 'updateProduct'])->name('admin.business.products.update');
    Route::post('/admin/business/{business}/products/{product}/walk-in-order', [AdminBusinessController::class, 'addWalkInOrder'])->name('admin.business.products.walkin-order');
    Route::patch('/admin/business/{business}/products/{product}/toggle-sold', [AdminBusinessController::class, 'toggleProductSold'])->name('admin.business.products.toggle-sold');
    Route::get('/admin/business/{business}/products/{product}/media', [AdminBusinessController::class, 'productMedia'])->name('admin.business.products.media');
        Route::patch('/admin/business/{business}/orders/{order}/complete', [AdminBusinessController::class, 'completeWalkInOrder'])->name('admin.business.orders.complete');
        Route::delete('/admin/business/{business}/orders/{order}', [AdminBusinessController::class, 'removeOrder'])->name('admin.business.orders.remove');
        Route::delete('/admin/business/{business}/history/{history}', [AdminBusinessController::class, 'removeMemberHistory'])->name('admin.business.history.remove');
    Route::post('/admin/business/{business}/subscriptions', [AdminBusinessController::class, 'storeSubscription'])->name('admin.business.subscriptions.store');
    Route::patch('/admin/business/{business}/subscriptions/{subscription}/expiry', [AdminBusinessController::class, 'setSubscriptionExpiry'])->name('admin.business.subscriptions.expiry');
    Route::post('/admin/business/{business}/reservations/walk-in', [AdminBusinessController::class, 'addWalkInReservation'])->name('admin.business.reservations.walkin');
    Route::patch('/admin/business/{business}/reservations/{reservation}/accept', [AdminBusinessController::class, 'acceptReservation'])->name('admin.business.reservations.accept');
    Route::delete('/admin/business/{business}/reservations/{reservation}', [AdminBusinessController::class, 'removeReservation'])->name('admin.business.reservations.remove');

    Route::get('/admin/profile', [ProfileController::class, 'editAdmin'])->name('admin.profile');
    Route::put('/admin/profile', [ProfileController::class, 'updateAdmin'])->name('admin.profile.update');
});
