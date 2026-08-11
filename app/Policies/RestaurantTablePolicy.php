<?php

namespace App\Policies;

use App\Models\RestaurantTable;
use App\Models\User;

class RestaurantTablePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function view(User $user, RestaurantTable $table): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function update(User $user, RestaurantTable $table): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function delete(User $user, RestaurantTable $table): bool
    {
        return $user->hasRole('admin');
    }

    public function generateQr(User $user, RestaurantTable $table): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }
}
