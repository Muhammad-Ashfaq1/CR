<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Contractor;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ImpersonationController extends Controller
{
    /**
     * Impersonate a user (Owner or Contractor).
     */
    public function impersonate(Request $request, User $user): RedirectResponse
    {
        $currentUser = Auth::user();
        $isActualAdmin = $currentUser->isAdmin() || session()->has('impersonator_id');

        if (! $isActualAdmin) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You are already logged in as this user.');
        }

        if ($user->isAdmin()) {
            return back()->with('error', 'Administrator accounts cannot be impersonated.');
        }

        // Store original admin ID if not already impersonating
        if (! session()->has('impersonator_id')) {
            session()->put('impersonator_id', $currentUser->id);
        }

        ActivityLogger::log([
            'event' => 'impersonate_user',
            'description' => "Started impersonating user {$user->name} (Role: {$user->role->label()})",
            'properties' => ['target_user_id' => $user->id, 'role' => $user->role->value],
        ], $user);

        Auth::login($user);

        return redirect()->route($user->defaultDashboardRoute())
            ->with('success', "You are now impersonating {$user->name} ({$user->role->label()}).");
    }

    /**
     * Impersonate a contractor directly from their contractor profile.
     */
    public function impersonateContractor(Request $request, Contractor $contractor): RedirectResponse
    {
        $currentUser = Auth::user();
        $isActualAdmin = $currentUser->isAdmin() || session()->has('impersonator_id');

        if (! $isActualAdmin) {
            abort(403, 'Unauthorized access.');
        }

        $user = $contractor->user;

        // If contractor doesn't have a linked user account, auto-provision one
        if (! $user) {
            $baseEmail = Str::slug($contractor->name, '.').'.'.$contractor->id.'@contractor.cr';
            $user = User::create([
                'name' => $contractor->name,
                'email' => $baseEmail,
                'password' => Hash::make(Str::random(24)),
                'role' => UserRole::Contractor,
                'phone' => $contractor->phone,
                'is_active' => true,
            ]);
            $user->assignRole(UserRole::Contractor->value);

            $contractor->user_id = $user->id;
            $contractor->save();
        }

        return $this->impersonate($request, $user);
    }

    /**
     * Leave impersonation and restore the original administrator session.
     */
    public function leave(Request $request): RedirectResponse
    {
        if (! session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $adminId = session()->pull('impersonator_id');
        $admin = User::find($adminId);

        if ($admin && $admin->isAdmin()) {
            Auth::login($admin);

            ActivityLogger::log([
                'event' => 'leave_impersonation',
                'description' => 'Exited impersonation mode and returned to admin session',
            ], $admin);

            return redirect()->route('admin.users.index')
                ->with('success', 'You have returned to your administrator account.');
        }

        Auth::logout();

        return redirect()->route('login')
            ->with('info', 'Impersonation ended. Please log in again.');
    }
}
