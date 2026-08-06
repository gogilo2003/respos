<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\Repositories\KitchenRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class KitchenDashboardService
{
    public function __construct(
        protected KitchenRepositoryInterface $kitchenRepository,
    ) {}

    /**
     * Full dashboard payload for the kitchen screen.
     *
     * @return array{
     *     pending_orders: array<int, mixed>,
     *     preparing_orders: array<int, mixed>,
     *     ready_orders: array<int, mixed>,
     *     statistics: array<string, mixed>,
     * }
     */
    public function getDashboardData(): array
    {
        return [
            'pending_orders'   => $this->shapeOrders($this->kitchenRepository->getPendingOrders()),
            'preparing_orders' => $this->shapeOrders($this->kitchenRepository->getPreparingOrders()),
            'ready_orders'     => $this->shapeOrders($this->kitchenRepository->getReadyOrders()),
            'statistics'       => $this->shapeStatistics($this->kitchenRepository->getDailyStatistics()),
        ];
    }

    /**
     * Shape a collection of Order models into the frontend-ready array.
     * Called identically for pending, preparing, and ready lists.
     */
    private function shapeOrders(Collection $orders): array
    {
        return $orders->map(function ($order) {
            $items = $order->items->map(fn ($item) => $this->shapeItem($item))->values();

            $counts = [
                'pending'   => $order->items->whereIn('status', ['pending', 'accepted'])->count(),
                'preparing' => $order->items->where('status', 'preparing')->count(),
                'ready'     => $order->items->where('status', 'ready')->count(),
                'served'    => $order->items->where('status', 'served')->count(),
            ];

            return [
                'order_id'    => $order->id,
                'session'     => [
                    'table_session_id' => $order->session->id,
                    'table_number'     => $order->session->table->table_number ?? null,
                ],
                'placed_at'   => $order->placed_at,
                'items'       => $items,
                'item_counts' => $counts,
            ];
        })->values()->all();
    }

    /**
     * Shape a single OrderItem model into the frontend-ready array.
     */
    private function shapeItem($item): array
    {
        return [
            'order_item_id'   => $item->id,
            'menu_item_id'    => $item->menu_item_id,
            'name'            => $item->menuItem->name ?? null,
            'quantity'        => $item->quantity,
            'status'          => $item->status,
            'unit_price'      => $item->unit_price,
            'sla_seconds_total' => ($item->ready_at && $item->accepted_at)
                ? $item->ready_at->diffInSeconds($item->accepted_at)
                : null,
        ];
    }

    /**
     * Shape raw repository statistics into a human-friendly payload.
     */
    private function shapeStatistics(array $raw): array
    {
        $avgSeconds = $raw['avg_prep_seconds'];

        return [
            'pending_items'    => $raw['pending_items'],
            'preparing_items'  => $raw['preparing_items'],
            'ready_items'      => $raw['ready_items'],
            'avg_prep_seconds' => $avgSeconds,
            'avg_prep_label'   => $avgSeconds !== null
                ? $this->formatSeconds($avgSeconds)
                : null,
        ];
    }

    /**
     * Convert a raw second count to a human-readable string (e.g. "4m 32s").
     */
    private function formatSeconds(int $seconds): string
    {
        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = intdiv($seconds, 60);
        $remaining = $seconds % 60;

        return $remaining > 0 ? "{$minutes}m {$remaining}s" : "{$minutes}m";
    }
}
