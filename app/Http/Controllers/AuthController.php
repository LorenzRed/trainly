<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'login')
                ->withInput($request->only('email'))
                ->with('open_login', true);
        }

        $credentials = $validator->validated();

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Invalid email or password.'], 'login')
                ->withInput($request->only('email'))
                ->with('open_login', true);
        }

        $request->session()->regenerate();

        $user = $request->user();

        if ($user && $user->role === 'admin') {
            return redirect()->route('admin.main');
        }

        return redirect()->route('users.main');
    }

    public function signup(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'signup')
                ->withInput($request->only('name', 'email'))
                ->with('open_signup', true);
        }

        $validated = $validator->validated();

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        return back()
            ->with('status', 'Account created successfully. Please login.')
            ->with('open_login', true);
    }

    public function createBusiness(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'business_name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'contact_number' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'business')
                ->withInput($request->only('business_name', 'full_name', 'email', 'contact_number'))
                ->with('open_business', true);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'admin',
            'business_name' => $validated['business_name'],
            'contact_number' => $validated['contact_number'],
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.main');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
