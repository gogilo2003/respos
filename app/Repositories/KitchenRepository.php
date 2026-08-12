<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\KitchenRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KitchenRepository extends BaseRepository implements KitchenRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Order);
    }

    // -------------------------------------------------------------------------
    // Shared query base
    // -------------------------------------------------------------------------

    /**
     * Eager-load the full graph every dashboard list needs, scoped to orders
     * inside currently-open sessions.  Returns a builder — callers add their
     * own whereHas / orderBy before calling ->get().
     */
    private function openOrdersQuery()
    {
        return Order::with(['session.table', 'items.menuItem'])
            ->whereHas('session', fn ($q) => $q->where('status', 'open'));
    }

    // -------------------------------------------------------------------------
    // Dashboard order lists
    // -------------------------------------------------------------------------

    public function getPendingOrders(): Collection
    {
        return $this->openOrdersQuery()
            ->whereHas('items', fn ($q) => $q->whereIn('status', ['pending', 'accepted']))
            ->orderBy('placed_at')
            ->get();
    }

    public function getPreparingOrders(): Collection
    {
        return $this->openOrdersQuery()
            ->whereHas('items', fn ($q) => $q->where('status', 'preparing'))
            ->orderBy('placed_at')
            ->get();
    }

    public function getReadyOrders(): Collection
    {
        return $this->openOrdersQuery()
            ->whereHas('items', fn ($q) => $q->where('status', 'ready'))
            ->orderBy('first_ready_at')
            ->get();
    }

    // -------------------------------------------------------------------------
    // Statistics
    // -------------------------------------------------------------------------

    public function getDailyStatistics(): array
    {
        $today = now()->startOfDay();

        // Single pass for live item counts across all open-session orders.
        $counts = OrderItem::selectRaw("
                SUM(CASE WHEN status IN ('pending','accepted') THEN 1 ELSE 0 END) AS pending_items,
                SUM(CASE WHEN status = 'preparing'            THEN 1 ELSE 0 END) AS preparing_items,
                SUM(CASE WHEN status = 'ready'               THEN 1 ELSE 0 END) AS ready_items
            ")
            ->whereHas('order.session', fn ($q) => $q->where('status', 'open'))
            ->first();

        // Average preparation time (accepted → ready) for items completed today.
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            $avgSeconds = OrderItem::whereNotNull('accepted_at')
                ->whereNotNull('ready_at')
                ->where('ready_at', '>=', $today)
                ->selectRaw('AVG(CAST((strftime(\'%s\', ready_at) - strftime(\'%s\', accepted_at)) AS INTEGER)) AS avg_seconds')
                ->value('avg_seconds');
        } else {
            $avgSeconds = OrderItem::whereNotNull('accepted_at')
                ->whereNotNull('ready_at')
                ->where('ready_at', '>=', $today)
                ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, accepted_at, ready_at)) AS avg_seconds')
                ->value('avg_seconds');
        }

        return [
            'pending_items' => (int) ($counts->pending_items ?? 0),
            'preparing_items' => (int) ($counts->preparing_items ?? 0),
            'ready_items' => (int) ($counts->ready_items ?? 0),
            'avg_prep_seconds' => $avgSeconds !== null ? (int) round((float) $avgSeconds) : null,
        ];
    }

    // -------------------------------------------------------------------------
    // Item-status mutation support
    // -------------------------------------------------------------------------

    public function refreshOrderItems(Order $order): Order
    {
        $order->load('items');

        return $order;
    }

    public function setOrderStatus(Order $order, string $status): void
    {
        $order->update(['status' => $status]);
    }
}
