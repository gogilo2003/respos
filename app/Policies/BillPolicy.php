<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function view(User $user, Bill $bill): bool
    {
        return $user->hasRole('cashier')
            || $user->hasRole('manager')
            || $user->hasRole('admin');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('cashier')
            || $user->hasRole('manager')
            || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('cashier')
            || $user->hasRole('manager')
            || $user->hasRole('admin');
    }

    public function void(User $user, Bill $bill): bool
    {
        return $user->hasRole('manager')
            || $user->hasRole('admin');
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $user->hasRole('manager')
            || $user->hasRole('admin');
    }
}
