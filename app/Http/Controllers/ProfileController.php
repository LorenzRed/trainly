<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        if ($request->user()?->role !== 'user') {
            abort(403);
        }

        return view('users.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        if ($request->user()?->role !== 'user') {
            abort(403);
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->business_name = $validated['business_name'] ?? null;
        $user->location = $validated['location'] ?? null;
        $user->contact_number = $validated['contact_number'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('users.profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    public function editAdmin(Request $request): View
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        return view('admin.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateAdmin(Request $request): RedirectResponse
    {
        if ($request->user()?->role !== 'admin') {
            abort(403);
        }

        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->business_name = !empty($validated['business_name']) ? strtoupper($validated['business_name']) : null;
        $user->location = $validated['location'] ?? null;
        $user->contact_number = $validated['contact_number'] ?? null;

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if (!empty($user->business_name)) {
            Business::where('user_id', $user->id)->update([
                'business_name' => $user->business_name,
            ]);
        }

        return redirect()
            ->route('admin.profile')
            ->with('status', 'Profile updated successfully.');
    }
}
