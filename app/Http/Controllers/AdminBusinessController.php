<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessMemberHistory;
use App\Models\BusinessOrder;
use App\Models\BusinessProduct;
use App\Models\BusinessReservation;
use App\Models\BusinessSubscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Database\Schema\Blueprint;

class AdminBusinessController extends Controller
{
    private const MANUAL_CLOSED_MARKER = '[[GYM_MANUAL_CLOSED]]';
    private const FACEBOOK_PAGE_PREFIX = '[[GYM_FACEBOOK_PAGE:';
    private const FACEBOOK_PAGE_SUFFIX = ']]';
    private const STOCK_PREFIX = '[[STOCK:';
    private const STOCK_SUFFIX = ']]';

    public function media(Request $request, Business $business, string $type)
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $relativePath = match ($type) {
            'logo' => $business->logo_path,
            'cover' => $business->cover_path,
            default => null,
        };

        if (!$relativePath) {
            abort(404);
        }
        $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $normalizedPath = preg_replace('#^storage/#', '', $normalizedPath);

        if (!Storage::disk('public')->exists($normalizedPath)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($normalizedPath));
    }

    public function userMedia(Request $request, Business $business, string $type)
    {
        if ($request->user()?->role !== 'user' || $business->user?->role !== 'admin') {
            abort(403);
        }

        $relativePath = match ($type) {
            'logo' => $business->logo_path,
            'cover' => $business->cover_path,
            default => null,
        };

        if (!$relativePath) {
            abort(404);
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $normalizedPath = preg_replace('#^storage/#', '', $normalizedPath);

        if (!Storage::disk('public')->exists($normalizedPath)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($normalizedPath));
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $business = Business::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'business_name' => $validated['business_name'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]
        );

        return redirect()->route('admin.business.settings', $business);
    }

    public function settings(Request $request, Business $business): View
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->ensureBusinessStatusColumn();

        $business->subscriptions()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $business->load([
            'products' => function ($query) {
                $query->latest();
            },
            'subscriptions' => function ($query) {
                $query->latest();
            },
            'reservations' => function ($query) {
                $query->with('user:id,name,email')->latest();
            },
        ]);

        $this->syncSoldOutStateWithStock($business);

        $this->syncActiveMemberHistories($business);

        $memberHistories = BusinessMemberHistory::query()
            ->with('reservation:id,payment_method')
            ->where('business_id', $business->id)
            ->latest('time_in')
            ->latest('id')
            ->limit(200)
            ->get();

        $storeOrders = BusinessOrder::query()
            ->with([
                'product:id,name,business_id',
                'subscription:id,duration,business_id',
                'user:id,name,email',
            ])
            ->where('business_id', $business->id)
            ->latest('ordered_at')
            ->latest('id')
            ->limit(200)
            ->get();

        return view('admin.business-settings', [
            'business' => $business,
            'memberHistories' => $memberHistories,
            'storeOrders' => $storeOrders,
        ]);
    }

    public function storeProduct(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:300'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'image' => ['required', 'image', 'max:8192'],
        ]);

        $imagePath = $request->file('image')->store('business-media/products', 'public');
        $descriptionWithStock = $this->applyProductStock((string) ($validated['description'] ?? ''), (int) $validated['stock']);

        $business->products()->create([
            'name' => $validated['name'],
            'description' => $descriptionWithStock,
            'price' => $validated['price'],
            'image_path' => $imagePath,
            'is_sold_out' => ((int) $validated['stock']) <= 0,
        ]);

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Product posted successfully.');
    }

    public function updateProduct(Request $request, Business $business, BusinessProduct $product): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $product->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'max:8192'],
        ]);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->image_path = $request->file('image')->store('business-media/products', 'public');
        }

        $product->name = $validated['name'];
        $product->price = $validated['price'];
        $product->description = $this->applyProductStock((string) ($product->description ?? ''), (int) $validated['stock']);
        $product->is_sold_out = ((int) $validated['stock']) <= 0;
        $product->save();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Product updated successfully.');
    }

    public function toggleProductSold(Request $request, Business $business, BusinessProduct $product): RedirectResponse
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id || (int) $product->getAttribute('business_id') !== $business->id) {
            abort(403);
        }

        $nextStatus = !$product->is_sold_out;
        if ($nextStatus === true) {
            $product->description = $this->applyProductStock((string) ($product->description ?? ''), 0);
            $product->is_sold_out = true;
            $product->save();

            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Product marked as sold out and stock set to 0.');
        }

        if ($this->getProductStock($product) <= 0) {
            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Cannot mark available when stock is 0. Please edit stock first.');
        }

        $product->is_sold_out = $nextStatus;
        $product->save();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Product availability updated.');
    }

    public function productMedia(Request $request, Business $business, BusinessProduct $product)
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id || (int) $product->getAttribute('business_id') !== $business->id) {
            abort(403);
        }

        $relativePath = $product->getAttribute('image_path');

        if (!$relativePath) {
            abort(404);
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $normalizedPath = preg_replace('#^storage/#', '', $normalizedPath);

        if (!Storage::disk('public')->exists($normalizedPath)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($normalizedPath));
    }

    public function userProductMedia(Request $request, Business $business, BusinessProduct $product)
    {
        if ($request->user()?->role !== 'user') {
            abort(403);
        }

        if ((int) $product->getAttribute('business_id') !== $business->id || $business->user?->role !== 'admin') {
            abort(404);
        }

        $relativePath = $product->getAttribute('image_path');

        if (!$relativePath) {
            abort(404);
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $relativePath), '/');
        $normalizedPath = preg_replace('#^storage/#', '', $normalizedPath);

        if (!Storage::disk('public')->exists($normalizedPath)) {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($normalizedPath));
    }

    public function storeSubscription(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'duration' => ['required', 'string', 'max:100'],
            'subscription_type' => ['required', 'in:gym_access,gym_trainer'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $business->subscriptions()->create([
            'duration' => $validated['duration'],
            'subscription_type' => $validated['subscription_type'],
            'price' => $validated['price'],
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Subscription added successfully.');
    }

    public function setSubscriptionExpiry(Request $request, Business $business, BusinessSubscription $subscription): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $subscription->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'expires_at' => ['required', 'date', 'after:now'],
        ]);

        $subscription->expires_at = $validated['expires_at'];
        $subscription->save();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Subscription expiry updated.');
    }

    public function updateSettings(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->ensureBusinessStatusColumn();

        $currentStatus = $this->getBusinessStatus($business);

        $validated = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'facebook_page_link' => ['nullable', 'url', 'max:255'],
            'capacity_limit' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'entrance_fee' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'monthly_access' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'rules' => ['nullable', 'string', 'max:2000'],
            'logo_image' => ['nullable', 'image', 'max:5120'],
            'cover_image' => ['nullable', 'image', 'max:8192'],
        ]);

        if (array_key_exists('location', $validated)) {
            $user = $request->user();
            $user->location = $validated['location'];
            $user->save();
        }

        if ($request->hasFile('logo_image')) {
            if ($business->logo_path) {
                Storage::disk('public')->delete($business->logo_path);
            }

            $validated['logo_path'] = $request->file('logo_image')->store('business-media/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($business->cover_path) {
                Storage::disk('public')->delete($business->cover_path);
            }

            $validated['cover_path'] = $request->file('cover_image')->store('business-media/covers', 'public');
        }

        $hasNotesPayload = array_key_exists('notes', $validated);
        $hasFacebookPayload = array_key_exists('facebook_page_link', $validated);

        if ($hasNotesPayload || $hasFacebookPayload) {
            $existingNotesWithoutManualMarker = $this->stripManualClosedMarker($business->notes);
            $existingNotesWithoutFacebookMarker = $this->stripFacebookPageLinkMarker($existingNotesWithoutManualMarker);
            $incomingNotes = $hasNotesPayload
                ? $validated['notes']
                : $existingNotesWithoutFacebookMarker;

            $facebookPageLink = $hasFacebookPayload
                ? $validated['facebook_page_link']
                : $this->extractFacebookPageLink($business->notes);

            $notesWithFacebook = $this->applyFacebookPageLink($incomingNotes, $facebookPageLink);
            $validated['notes'] = $currentStatus === 'closed'
                ? $this->applyManualClosedMarker($notesWithFacebook)
                : $notesWithFacebook;
        }

        unset($validated['logo_image'], $validated['cover_image'], $validated['location'], $validated['facebook_page_link']);

        $business->update($validated);

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Business settings updated successfully.');
    }

    public function toggleGymStatus(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $this->ensureBusinessStatusColumn();

        $currentlyClosed = $this->getBusinessStatus($business) === 'closed';
        $cleanNotes = $this->stripManualClosedMarker($business->notes);

        if ($currentlyClosed) {
            $business->status = 'open';
            $business->notes = $cleanNotes;
            $statusMessage = 'Gym status set to Open.';
        } else {
            $business->status = 'closed';
            $business->notes = $this->applyManualClosedMarker($cleanNotes);
            $statusMessage = 'Gym status set to Closed.';
        }

        $business->save();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', $statusMessage);
    }

    public function reserveGym(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'user' || $business->user?->role !== 'admin') {
            abort(403);
        }

        $this->ensureBusinessStatusColumn();

        if ($this->getBusinessStatus($business) === 'closed') {
            return redirect()
                ->route('users.gym.view', $business)
                ->with('status', 'Gym is currently closed. Reservation is unavailable.')
                ->with('status_type', 'error');
        }

        if (!$this->hasSubscriptionAccess($business, (int) $request->user()->id)) {
            $request->validate([
                'gcash_name' => ['required', 'string', 'max:120'],
                'gcash_number' => ['required', 'string', 'max:20'],
                'gcash_reference' => ['required', 'string', 'max:80'],
            ]);
        }

        $reservation = BusinessReservation::firstOrCreate(
            [
                'business_id' => $business->id,
                'user_id' => $request->user()->id,
            ],
            [
                'reserved_at' => now(),
                'accepted_at' => now(),
                'payment_method' => 'online_gcash',
            ]
        );

        if (!$reservation->payment_method) {
            $reservation->payment_method = 'online_gcash';
        }

        if (!$reservation->accepted_at) {
            $reservation->accepted_at = now();
        }

        $reservation->save();

        return redirect()
            ->route('users.gym.view', $business)
            ->with('status', 'Gym reservation confirmed.')
            ->with('status_type', 'success');
    }

    public function cancelGymReservation(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'user' || $business->user?->role !== 'admin') {
            abort(403);
        }

        $reservation = BusinessReservation::query()
            ->where('business_id', $business->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$reservation) {
            return redirect()
                ->route('users.gym.view', $business)
                ->with('status', 'No reservation found to cancel.')
                ->with('status_type', 'error');
        }

        if ($reservation->accepted_at) {
            $this->ensureHistoryTimeOut($business, $reservation, 'Cancelled by member.');
        }

        $reservation->delete();

        return redirect()
            ->route('users.gym.view', $business)
            ->with('status', 'Reservation cancelled successfully.')
            ->with('status_type', 'success');
    }

    public function addWalkInOrder(Request $request, Business $business, BusinessProduct $product): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $product->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $stock = $this->getProductStock($product);

        if ($stock < $quantity) {
            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Not enough stock for walk-in order.');
        }

        $unitPrice = (float) $product->price;
        $totalPrice = $unitPrice * $quantity;

        BusinessOrder::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'user_id' => null,
            'customer_name' => 'Walk-in Customer',
            'customer_email' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'payment_method' => 'walk_in_payment',
            'payment_status' => 'paid',
            'ordered_at' => now(),
        ]);

        $this->decrementProductStock($product, $quantity);

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Walk-in order recorded.');
    }

    public function completeWalkInOrder(Request $request, Business $business, BusinessOrder $order): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $order->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        $canComplete = $order->payment_method === 'walk_in_payment'
            || ($order->payment_method === 'online_gcash' && !is_null($order->subscription_id));

        if (!$canComplete) {
            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Only walk-in and subscription GCash orders can be completed from this button.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Order is already completed.');
        }

        $order->payment_status = 'paid';
        $order->save();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Order completed.');
    }

    public function removeMemberHistory(Request $request, Business $business, BusinessMemberHistory $history): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $history->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        $history->delete();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Member history removed.');
    }

    public function removeOrder(Request $request, Business $business, BusinessOrder $order): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $order->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        if ($order->payment_status !== 'paid') {
            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Only completed orders can be removed.');
        }

        $order->delete();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Completed order removed.');
    }

    public function placeOnlineOrder(Request $request, Business $business, BusinessProduct $product): RedirectResponse
    {
        if ($request->user()?->role !== 'user' || $business->user?->role !== 'admin') {
            abort(403);
        }

        if ((int) $product->getAttribute('business_id') !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'payment_method' => ['required', 'in:walk_in_payment,online_gcash'],
            'gcash_name' => ['nullable', 'string', 'max:120', 'required_if:payment_method,online_gcash'],
            'gcash_number' => ['nullable', 'string', 'max:20', 'required_if:payment_method,online_gcash'],
            'gcash_reference' => ['nullable', 'string', 'max:80', 'required_if:payment_method,online_gcash'],
        ]);

        $quantity = (int) ($validated['quantity'] ?? 1);
        $stock = $this->getProductStock($product);
        $paymentMethod = (string) ($validated['payment_method'] ?? 'online_gcash');

        if ($this->hasPendingStorePayment($business, (int) $request->user()->id) && $paymentMethod === 'walk_in_payment') {
            return redirect()
                ->route('users.gym.view', $business)
                ->with('status', 'You have a pending pay at gym order. You can continue only with GCash.')
                ->with('status_type', 'error');
        }

        if ($stock < $quantity) {
            return redirect()
                ->route('users.gym.view', $business)
                ->with('status', 'Not enough stock for this order.')
                ->with('status_type', 'error');
        }

        $unitPrice = (float) $product->price;
        $totalPrice = $unitPrice * $quantity;
        $isGcash = $paymentMethod === 'online_gcash';

        BusinessOrder::create([
            'business_id' => $business->id,
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'customer_name' => $isGcash
                ? trim((string) ($validated['gcash_name'] ?? $request->user()->name))
                : (string) $request->user()->name,
            'customer_email' => (string) $request->user()->email,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'payment_method' => $paymentMethod,
            'payment_status' => $isGcash ? 'paid' : 'pay_at_gym',
            'ordered_at' => now(),
        ]);

        $this->decrementProductStock($product, $quantity);

        return redirect()
            ->route('users.gym.view', $business)
            ->with('status', $isGcash ? 'Order placed via GCash.' : 'Order placed. Please pay at the gym (walk-in).')
            ->with('status_type', 'success');
    }

    public function placeSubscriptionOrder(Request $request, Business $business, BusinessSubscription $subscription): RedirectResponse
    {
        if ($request->user()?->role !== 'user' || $business->user?->role !== 'admin') {
            abort(403);
        }

        if ((int) $subscription->getAttribute('business_id') !== $business->id) {
            abort(404);
        }

        $validated = $request->validate([
            'gcash_name' => ['required', 'string', 'max:120'],
            'gcash_number' => ['required', 'string', 'max:20'],
            'gcash_reference' => ['required', 'string', 'max:80'],
        ]);

        BusinessOrder::create([
            'business_id' => $business->id,
            'subscription_id' => $subscription->id,
            'product_id' => null,
            'user_id' => $request->user()->id,
            'customer_name' => (string) $request->user()->name,
            'customer_email' => (string) $request->user()->email,
            'quantity' => 1,
            'unit_price' => (float) $subscription->price,
            'total_price' => (float) $subscription->price,
            'payment_method' => 'online_gcash',
            'payment_status' => 'paid',
            'gcash_name' => $validated['gcash_name'],
            'gcash_number' => $validated['gcash_number'],
            'gcash_reference' => $validated['gcash_reference'],
            'ordered_at' => now(),
        ]);

        // Extend subscription expiration date based on duration
        $currentExpiresAt = $subscription->expires_at ? Carbon::parse($subscription->expires_at) : now();
        $newExpiresAt = $this->extendDateByDuration($currentExpiresAt, $subscription->duration);
        $subscription->update(['expires_at' => $newExpiresAt]);

        return redirect()
            ->route('users.gym.view', $business)
            ->with('status', 'Subscription buy request submitted via GCash.')
            ->with('status_type', 'success');
    }

    private function hasManualClosedMarker(?string $notes): bool
    {
        return is_string($notes) && str_contains($notes, self::MANUAL_CLOSED_MARKER);
    }

    private function extendDateByDuration(Carbon $baseDate, ?string $duration): Carbon
    {
        if (!$duration) {
            return $baseDate->addMonth();
        }

        $durationLower = strtolower(trim($duration));
        
        // Extract numbers from the duration string
        if (preg_match('/(\d+)\s*(day|week|month|year)s?/i', $durationLower, $matches)) {
            $amount = (int) $matches[1];
            $unit = strtolower($matches[2]);
            
            return match ($unit) {
                'day' => $baseDate->addDays($amount),
                'week' => $baseDate->addWeeks($amount),
                'month' => $baseDate->addMonths($amount),
                'year' => $baseDate->addYears($amount),
                default => $baseDate->addMonth(),
            };
        }
        
        // Default to 1 month if parsing fails
        return $baseDate->addMonth();
    }

    private function getBusinessStatus(Business $business): string
    {
        $status = strtolower(trim((string) ($business->status ?? '')));

        if (in_array($status, ['open', 'closed'], true)) {
            return $status;
        }

        return $this->hasManualClosedMarker($business->notes) ? 'closed' : 'open';
    }

    private function ensureBusinessStatusColumn(): void
    {
        if (!Schema::hasColumn('businesses', 'status')) {
            Schema::table('businesses', function (Blueprint $table): void {
                $table->string('status', 20)->default('open')->after('business_name');
            });
        }

        DB::statement("UPDATE businesses SET status = CASE WHEN notes LIKE '%[[GYM_MANUAL_CLOSED]]%' THEN 'closed' ELSE 'open' END WHERE status IS NULL OR status = ''");
    }

    private function stripManualClosedMarker(?string $notes): ?string
    {
        if (!is_string($notes)) {
            return $notes;
        }

        $stripped = str_replace(self::MANUAL_CLOSED_MARKER, '', $notes);
        $stripped = trim($stripped);

        return $stripped === '' ? null : $stripped;
    }

    private function applyManualClosedMarker(?string $notes): string
    {
        $clean = $this->stripManualClosedMarker($notes);
        return trim(($clean ? $clean . ' ' : '') . self::MANUAL_CLOSED_MARKER);
    }

    private function extractFacebookPageLink(?string $notes): ?string
    {
        if (!is_string($notes)) {
            return null;
        }

        $pattern = '/' . preg_quote(self::FACEBOOK_PAGE_PREFIX, '/') . '(.*?)' . preg_quote(self::FACEBOOK_PAGE_SUFFIX, '/') . '/';
        if (!preg_match($pattern, $notes, $matches)) {
            return null;
        }

        $value = trim((string) ($matches[1] ?? ''));
        return $value === '' ? null : $value;
    }

    private function stripFacebookPageLinkMarker(?string $notes): ?string
    {
        if (!is_string($notes)) {
            return $notes;
        }

        $pattern = '/' . preg_quote(self::FACEBOOK_PAGE_PREFIX, '/') . '.*?' . preg_quote(self::FACEBOOK_PAGE_SUFFIX, '/') . '/';
        $stripped = preg_replace($pattern, '', $notes);
        $stripped = trim((string) $stripped);

        return $stripped === '' ? null : $stripped;
    }

    private function applyFacebookPageLink(?string $notes, ?string $facebookPageLink): ?string
    {
        $cleanNotes = $this->stripFacebookPageLinkMarker($notes);
        $cleanLink = trim((string) $facebookPageLink);

        if ($cleanLink === '') {
            return $cleanNotes;
        }

        return trim(($cleanNotes ? $cleanNotes . ' ' : '') . self::FACEBOOK_PAGE_PREFIX . $cleanLink . self::FACEBOOK_PAGE_SUFFIX);
    }

    private function extractProductStock(?string $description): int
    {
        $value = (string) $description;
        $pattern = '/' . preg_quote(self::STOCK_PREFIX, '/') . '(\\d+)' . preg_quote(self::STOCK_SUFFIX, '/') . '/';

        if (!preg_match($pattern, $value, $matches)) {
            return 0;
        }

        return max(0, (int) ($matches[1] ?? 0));
    }

    private function stripProductStockMarker(?string $description): ?string
    {
        if (!is_string($description)) {
            return $description;
        }

        $pattern = '/' . preg_quote(self::STOCK_PREFIX, '/') . '\\d+' . preg_quote(self::STOCK_SUFFIX, '/') . '/';
        $stripped = trim((string) preg_replace($pattern, '', $description));

        return $stripped === '' ? null : $stripped;
    }

    private function applyProductStock(?string $description, int $stock): string
    {
        $cleanDescription = $this->stripProductStockMarker($description);
        $safeStock = max(0, $stock);

        return trim(($cleanDescription ? $cleanDescription . ' ' : '') . self::STOCK_PREFIX . $safeStock . self::STOCK_SUFFIX);
    }

    private function getProductStock(BusinessProduct $product): int
    {
        return $this->extractProductStock((string) ($product->description ?? ''));
    }

    private function syncSoldOutStateWithStock(Business $business): void
    {
        foreach ($business->products as $product) {
            $shouldBeSoldOut = $this->getProductStock($product) <= 0;
            if ($product->is_sold_out !== $shouldBeSoldOut) {
                $product->is_sold_out = $shouldBeSoldOut;
                $product->save();
            }
        }
    }

    private function hasPendingStorePayment(Business $business, int $userId): bool
    {
        return BusinessOrder::query()
            ->where('business_id', $business->id)
            ->where('user_id', $userId)
            ->whereNotNull('product_id')
            ->where('payment_status', 'pay_at_gym')
            ->exists();
    }

    private function hasSubscriptionAccess(Business $business, int $userId): bool
    {
        return BusinessOrder::query()
            ->where('business_id', $business->id)
            ->where('user_id', $userId)
            ->whereNotNull('subscription_id')
            ->whereIn('payment_status', ['pending', 'paid'])
            ->exists();
    }

    private function decrementProductStock(BusinessProduct $product, int $quantity): void
    {
        $currentStock = $this->getProductStock($product);
        $newStock = max(0, $currentStock - max(1, $quantity));
        $product->description = $this->applyProductStock((string) ($product->description ?? ''), $newStock);

        if ($newStock <= 0) {
            $product->is_sold_out = true;
        }

        $product->save();
    }

    public function acceptReservation(Request $request, Business $business, BusinessReservation $reservation): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $reservation->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        if ($reservation->accepted_at) {
            return redirect()
                ->route('admin.business.settings', $business)
                ->with('status', 'Reservation already accepted.');
        }

        $capacityLimit = (int) ($business->capacity_limit ?? 0);
        if ($capacityLimit > 0) {
            $acceptedCount = BusinessReservation::query()
                ->where('business_id', $business->id)
                ->whereNotNull('accepted_at')
                ->count();

            if ($acceptedCount >= $capacityLimit) {
                return redirect()
                    ->route('admin.business.settings', $business)
                    ->with('status', 'Gym is already at maximum capacity.');
            }
        }

        $reservation->update([
            'accepted_at' => now(),
        ]);

        $this->ensureHistoryTimeIn($business, $reservation, false);

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Reservation accepted.');
    }

    public function removeReservation(Request $request, Business $business, BusinessReservation $reservation): RedirectResponse
    {
        if (
            $request->user()?->role !== 'admin'
            || $business->user_id !== $request->user()->id
            || (int) $reservation->getAttribute('business_id') !== $business->id
        ) {
            abort(403);
        }

        $this->ensureHistoryTimeOut($business, $reservation, 'Ended by admin (time ended or kicked).');

        $reservation->delete();

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Reservation removed.');
    }

    public function addWalkInReservation(Request $request, Business $business): RedirectResponse
    {
        if ($request->user()?->role !== 'admin' || $business->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'walkin_name' => ['required', 'string', 'max:120'],
        ]);

        $capacityLimit = (int) ($business->capacity_limit ?? 0);
        if ($capacityLimit > 0) {
            $acceptedCount = BusinessReservation::query()
                ->where('business_id', $business->id)
                ->whereNotNull('accepted_at')
                ->count();

            if ($acceptedCount >= $capacityLimit) {
                return redirect()
                    ->route('admin.business.settings', $business)
                    ->with('status', 'Gym is already at maximum capacity.');
            }
        }

        $walkInToken = now()->format('YmdHis') . '-' . mt_rand(1000, 9999);
        $walkInName = trim((string) $validated['walkin_name']);
        $walkInUser = User::create([
            'role' => 'user',
            'name' => $walkInName,
            'email' => 'walkin.' . $business->id . '.' . $walkInToken . '@trainly.local',
            'password' => Hash::make(bin2hex(random_bytes(16))),
        ]);

        $reservation = BusinessReservation::create([
            'business_id' => $business->id,
            'user_id' => $walkInUser->id,
            'reserved_at' => now(),
            'accepted_at' => now(),
            'payment_method' => 'walk_in_payment',
        ]);

        $this->ensureHistoryTimeIn($business, $reservation, true);

        return redirect()
            ->route('admin.business.settings', $business)
            ->with('status', 'Walk-in reservation added.');
    }

    private function ensureHistoryTimeIn(Business $business, BusinessReservation $reservation, bool $isWalkIn): void
    {
        $timeIn = $reservation->accepted_at ?: $reservation->reserved_at ?: $reservation->created_at ?: now();

        $history = BusinessMemberHistory::query()
            ->where('business_id', $business->id)
            ->where('reservation_id', $reservation->id)
            ->first();

        if ($history) {
            if (!$history->time_in) {
                $history->time_in = $timeIn;
            }
            $history->is_walk_in = $isWalkIn;
            $history->member_name = $reservation->user?->name ?: 'Unknown User';
            $history->member_email = $reservation->user?->email;
            $history->save();
            return;
        }

        BusinessMemberHistory::create([
            'business_id' => $business->id,
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'member_name' => $reservation->user?->name ?: 'Unknown User',
            'member_email' => $reservation->user?->email,
            'is_walk_in' => $isWalkIn,
            'time_in' => $timeIn,
        ]);
    }

    private function ensureHistoryTimeOut(Business $business, BusinessReservation $reservation, string $reason): void
    {
        $history = BusinessMemberHistory::query()
            ->where('business_id', $business->id)
            ->where('reservation_id', $reservation->id)
            ->first();

        if (!$history) {
            $this->ensureHistoryTimeIn($business, $reservation, str_starts_with(strtolower((string) ($reservation->user?->email ?? '')), 'walkin.'));
            $history = BusinessMemberHistory::query()
                ->where('business_id', $business->id)
                ->where('reservation_id', $reservation->id)
                ->first();
        }

        if (!$history) {
            return;
        }

        if (!$history->time_in) {
            $history->time_in = $reservation->accepted_at ?: $reservation->reserved_at ?: $reservation->created_at ?: now();
        }

        $history->time_out = now();
        $history->time_out_reason = $reason;
        $history->save();
    }

    private function syncActiveMemberHistories(Business $business): void
    {
        $activeReservations = BusinessReservation::query()
            ->with('user:id,name,email')
            ->where('business_id', $business->id)
            ->whereNotNull('accepted_at')
            ->get();

        foreach ($activeReservations as $reservation) {
            if (!$reservation instanceof BusinessReservation) {
                continue;
            }

            $email = strtolower((string) ($reservation->user?->email ?? ''));
            $isWalkIn = str_starts_with($email, 'walkin.');
            $this->ensureHistoryTimeIn($business, $reservation, $isWalkIn);
        }
    }
}
