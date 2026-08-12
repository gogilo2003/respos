<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('orders.index');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermission('orders.index');
    }

    public function create(User $user): bool
    {
        return in_array($user->role?->name, ['admin', 'manager', 'waiter', 'customer'], true);
    }

    public function transition(User $user, Order $order): bool
    {
        if ($user->hasRole('kitchen') && $order->status === 'pending') {
            return false;
        }

        if ($order->status === 'served' && $user->hasRole('kitchen')) {
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

    public function kitchen(User $user): bool
    {
        return $user->hasPermission('kitchen.dashboard') || $user->hasPermission('orders.index');
    }

    public function waiter(User $user): bool
    {
        return $user->hasPermission('waiter.dashboard') || $user->hasPermission('orders.index');
    }
}
