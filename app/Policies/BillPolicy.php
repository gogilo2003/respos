<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    public function view(User $user, Bill $bill): bool
    {
        return $user->hasPermission('bills.index');
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('bills.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('bills.index');
    }

    public function void(User $user, Bill $bill): bool
    {
        return $user->hasPermission('bills.void');
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $user->hasPermission('bills.void');
    }
}
