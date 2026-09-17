<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = UserRole::cases();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = UserRole::cases();

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contractor_address' => ['nullable', 'string', 'max:500'],
            'contractor_cnic' => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        if ($user->isContractor()) {
            Contractor::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'company_name' => $validated['company_name'] ?? null,
                'cnic' => $validated['contractor_cnic'] ?? null,
                'address' => $validated['contractor_address'] ?? null,
                'is_active' => $user->is_active,
            ]);
        }

        ActivityLogger::log([
            'event' => 'user_created',
            'description' => "Created user {$user->name} ({$user->email}) with role {$user->role->value}",
            'properties' => ['user_id' => $user->id, 'role' => $user->role->value],
        ], $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} created successfully.");
    }

    public function edit(User $user): View
    {
        $roles = UserRole::cases();
        $user->load('contractorProfile');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'phone' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'contractor_address' => ['nullable', 'string', 'max:500'],
            'contractor_cnic' => ['nullable', 'string', 'max:30'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->phone = $validated['phone'] ?? null;
        $user->is_active = $request->boolean('is_active', true);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->syncRoles([$validated['role']]);

        if ($user->isContractor()) {
            Contractor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'company_name' => $validated['company_name'] ?? null,
                    'cnic' => $validated['contractor_cnic'] ?? null,
                    'address' => $validated['contractor_address'] ?? null,
                    'is_active' => $user->is_active,
                ]
            );
        }

        ActivityLogger::log([
            'event' => 'user_updated',
            'description' => "Updated user {$user->name} ({$user->email})",
            'properties' => ['user_id' => $user->id],
        ], $user);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} updated successfully.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;

        ActivityLogger::log([
            'event' => 'user_deleted',
            'description' => "Deleted user {$userName} ({$user->email})",
            'properties' => ['user_id' => $user->id],
        ]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} deleted successfully.");
    }
}
