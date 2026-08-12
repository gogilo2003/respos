<?php

namespace App\Policies;

use App\Models\RestaurantTable;
use App\Models\User;

class RestaurantTablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('tables.index');
    }

    public function view(User $user, RestaurantTable $table): bool
    {
        return $user->hasPermission('tables.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('tables.index');
    }

    public function update(User $user, RestaurantTable $table): bool
    {
        return $user->hasPermission('tables.index');
    }

    public function delete(User $user, RestaurantTable $table): bool
    {
        return $user->hasPermission('tables.index');
    }

    public function generateQr(User $user, RestaurantTable $table): bool
    {
        return $user->hasPermission('tables.index');
    }
}
