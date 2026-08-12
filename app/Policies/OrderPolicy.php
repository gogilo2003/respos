<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'cashier', 'waiter', 'kitchen'], true);
    }

    public function view(User $user, Order $order): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'cashier', 'waiter', 'kitchen'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'waiter', 'customer'], true);
    }

    public function transition(User $user, Order $order): bool
    {
        // Kitchen staff cannot accept new pending orders (waiter accepts)
        if ($user->hasRole('kitchen') && $order->status === 'pending') {
            return false;
        }

        return in_array($user->role?->name, ['admin', 'manager', 'waiter', 'kitchen'], true);
    }

    public function update(User $user, Order $order): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'waiter', 'kitchen'], true);
    }

    public function cancel(User $user, Order $order): bool
    {
        return in_array($user->role?->name, ['admin', 'manager'], true);
    }
}
