<?php

namespace App\Policies;

use App\Models\MenuItem;
use App\Models\User;

class MenuItemPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'cashier', 'waiter', 'kitchen'], true);
    }

    public function view(User $user, MenuItem $item): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'cashier', 'waiter', 'kitchen'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }

    public function update(User $user, MenuItem $item): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }

    public function delete(User $user, MenuItem $item): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }

    public function toggleAvailability(User $user, MenuItem $item): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'kitchen'], true);
    }
}
