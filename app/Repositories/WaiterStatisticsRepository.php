<?php

namespace App\Repositories;

use App\Interfaces\Repositories\WaiterStatisticsRepositoryInterface;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Support\Collection;

class WaiterStatisticsRepository extends BaseRepository implements WaiterStatisticsRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new TableSession());
    }

    public function getOccupiedTableCount(): int
    {
        return $this->model->whereIn('status', ['open', 'billing'])->count();
    }

    public function getPendingOrderCount(array $sessionIds): int
    {
        return Order::whereIn('session_id', $sessionIds)
            ->where('status', 'pending')
            ->count();
    }

    public function getReadyOrderCount(array $sessionIds): int
    {
        return Order::whereIn('session_id', $sessionIds)
            ->where('status', 'ready')
            ->count();
    }

    public function getCompletedTodayCount(array $sessionIds): int
    {
        $today = now()->startOfDay();

        return Order::whereIn('session_id', $sessionIds)
            ->where(function ($query) use ($today) {
                $query->where('ready_at', '>=', $today)
                    ->orWhere('fully_served_at', '>=', $today);
            })
            ->count();
    }

    public function getOpenAssistanceRequestCount(): int
    {
        return $this->model->whereHas('assistanceRequests', function ($query) {
            $query->where('status', 'open');
        })->count();
    }
}
