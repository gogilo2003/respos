<?php

namespace App\Http\Controllers;

use App\Interfaces\Repositories\RoleRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
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

        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password']);

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

        if (!empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
        }
        unset($validated['password']);

        $this->userRepository->update($id, $validated);

        return redirect()->back()->with('message', 'User updated successfully.');
    }

    public function destroy($id)
    {
        Gate::authorize('admin');

        $this->userRepository->delete($id);

        return redirect()->back()->with('message', 'User deleted successfully.');
    }
}
