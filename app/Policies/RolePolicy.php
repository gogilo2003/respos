<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('roles.index');
    }

    public function update(User $user, ?Role $role = null): bool
    {
        return $user->hasPermission('roles.index');
    }
}
