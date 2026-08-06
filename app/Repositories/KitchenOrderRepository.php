<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\Repositories\KitchenOrderRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class KitchenOrderRepository extends BaseRepository implements KitchenOrderRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Order());
    }

    /**
     * Base query: orders in open sessions, eager-loaded for the dashboard.
     */
    private function baseQuery()
    {
        return Order::with(['session.table', 'items.menuItem'])
            ->whereHas('session', fn ($q) => $q->where('status', 'open'));
    }

    public function getPendingOrders(): Collection
    {
        return $this->baseQuery()
            ->whereHas('items', fn ($q) => $q->whereIn('status', ['pending', 'accepted']))
            ->orderBy('placed_at')
            ->get();
    }

    public function getPreparingOrders(): Collection
    {
        return $this->baseQuery()
            ->whereHas('items', fn ($q) => $q->where('status', 'preparing'))
            ->orderBy('placed_at')
            ->get();
    }

    public function getReadyOrders(): Collection
    {
        return $this->baseQuery()
            ->whereHas('items', fn ($q) => $q->where('status', 'ready'))
            ->orderBy('first_ready_at')
            ->get();
    }

    public function getDailyStatistics(): array
    {
        $today = now()->startOfDay();

        $itemCounts = OrderItem::selectRaw("
                SUM(CASE WHEN status IN ('pending','accepted') THEN 1 ELSE 0 END) as pending_items,
                SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END)             as preparing_items,
                SUM(CASE WHEN status = 'ready'     THEN 1 ELSE 0 END)             as ready_items
            ")
            ->whereHas('order.session', fn ($q) => $q->where('status', 'open'))
            ->first();

        // Average seconds between accepted_at and ready_at for items completed today.
        $avgPrep = OrderItem::whereNotNull('accepted_at')
            ->whereNotNull('ready_at')
            ->where('ready_at', '>=', $today)
            ->selectRaw('AVG(' . DB::raw('TIMESTAMPDIFF(SECOND, accepted_at, ready_at)') . ') as avg_seconds')
            ->value('avg_seconds');

        return [
            'pending_items'   => (int) ($itemCounts->pending_items ?? 0),
            'preparing_items' => (int) ($itemCounts->preparing_items ?? 0),
            'ready_items'     => (int) ($itemCounts->ready_items ?? 0),
            'avg_prep_seconds' => $avgPrep !== null ? (int) round($avgPrep) : null,
        ];
    }
}
