<?php

namespace App\Policies;

use App\Models\CashReconciliation;
use App\Models\User;

class CashReconciliationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('cashier') || $user->hasRole('manager') || $user->hasRole('admin');
    }

    public function view(User $user, CashReconciliation $reconciliation): bool
    {
        return $user->hasRole('cashier') || $user->hasRole('manager') || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('cashier') || $user->hasRole('manager') || $user->hasRole('admin');
    }

    public function approve(User $user, CashReconciliation $reconciliation): bool
    {
        return $user->hasRole('manager') || $user->hasRole('admin');
    }
}
