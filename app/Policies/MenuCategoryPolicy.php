<?php

namespace App\Policies;

use App\Models\MenuCategory;
use App\Models\User;

class MenuCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'cashier', 'waiter', 'kitchen'], true);
    }

    public function view(User $user, MenuCategory $category): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'cashier', 'waiter', 'kitchen'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }

    public function update(User $user, MenuCategory $category): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }

    public function delete(User $user, MenuCategory $category): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }

    public function toggleActive(User $user, MenuCategory $category): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }
}
