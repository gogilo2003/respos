<?php

namespace App\Policies;

use App\Models\CashReconciliation;
use App\Models\User;

class CashReconciliationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('reconciliations.index');
    }

    public function view(User $user, CashReconciliation $reconciliation): bool
    {
        return $user->hasPermission('reconciliations.index');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('reconciliations.index');
    }

    public function approve(User $user, ?CashReconciliation $reconciliation = null): bool
    {
        return $user->hasPermission('reconciliations.approve');
    }
}
