<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\RoleRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    protected UserRepositoryInterface $userRepository;

    protected RoleRepositoryInterface $roleRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
    }

    public function index()
    {
        Gate::authorize('admin');

        return Inertia::render('Users/Index', [
            'users' => $this->userRepository->all()->load('role'),
            'roles' => $this->roleRepository->all(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:60|unique:users',
            'email' => 'nullable|email|max:100|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        $this->userRepository->create($validated);

        return redirect()->back()->with('message', 'User created successfully.');
    }

    public function update(Request $request, $id)
    {
        Gate::authorize('admin');

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'name' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:60', Rule::unique('users')->ignore($id)],
            'email' => ['nullable', 'email', 'max:100', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'required|boolean',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $this->userRepository->update($id, $validated);

        return redirect()->back()->with('message', 'User updated successfully.');
    }

    public function toggleStatus(User $user)
    {
        Gate::authorize('admin');

        $this->userRepository->update($user->id, [
            'is_active' => ! $user->is_active,
        ]);

        app(AuditLogService::class)->log(
            'user_status_toggled',
            'User',
            $user->id,
            ['is_active' => $user->is_active],
            ['is_active' => ! $user->is_active],
            'User account active status toggled by admin'
        );

        $statusLabel = ! $user->is_active ? 'activated' : 'suspended';

        return redirect()->back()->with('message', "User account {$statusLabel} successfully.");
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $this->userRepository->delete($id);

        return redirect()->back()->with('message', 'User deleted successfully.');
    }
}
