<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderTransitionService
{
    /**
     * Transition order and its item statuses.
     */
    public function transition(Order $order, string $targetStatus): Order
    {
        $allowed = ['pending', 'accepted', 'preparing', 'ready', 'served', 'completed', 'cancelled'];
        if (! in_array($targetStatus, $allowed, true)) {
            throw new \InvalidArgumentException("Invalid order status: {$targetStatus}");
        }

        DB::transaction(function () use ($order, $targetStatus) {
            $order->update([
                'status' => $targetStatus,
            ]);

            $itemStatusMap = [
                'accepted' => 'accepted',
                'preparing' => 'preparing',
                'ready' => 'ready',
                'served' => 'served',
                'completed' => 'served',
                'cancelled' => 'cancelled',
            ];

            if (isset($itemStatusMap[$targetStatus])) {
                $now = now();
                $updateData = ['status' => $itemStatusMap[$targetStatus]];

                if ($targetStatus === 'accepted') {
                    $updateData['accepted_at'] = $now;
                } elseif ($targetStatus === 'ready') {
                    $updateData['ready_at'] = $now;
                }

                $order->items()->update($updateData);
            }
        });

        return $order->fresh(['items', 'session.table']);
    }
}
