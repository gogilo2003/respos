<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Models\Order;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
    ) {}

    /**
     * Mark an order as ready (all items are ready).
     *
     * Sets status = 'ready' and stamps first_ready_at if not already set.
     */
    public function markOrderReady(Order $order): bool
    {
        $timestamps = [];

        if (! $order->first_ready_at) {
            $timestamps['first_ready_at'] = now();
        }

        return (bool) $this->orderRepository->updateStatus(
            $order->id,
            'ready',
            $timestamps ?: null,
        );
    }

    /**
     * Mark an order as fully served (all items delivered to the table).
     *
     * Sets status = 'served' and stamps fully_served_at.
     */
    public function markOrderFullyServed(Order $order): bool
    {
        return (bool) $this->orderRepository->updateStatus(
            $order->id,
            'served',
            ['fully_served_at' => now()],
        );
    }

    /**
     * Reopen an order for preparation after new items are added
     * to a served or billed order.
     */
    public function reopenOrderForPreparation(Order $order): bool
    {
        if ($order->status !== 'served') {
            return false;
        }

        return (bool) $this->orderRepository->updateStatus(
            $order->id,
            'accepted',
            ['accepted_at' => now()],
        );
    }
}
