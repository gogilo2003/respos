<?php

namespace App\Services;

use App\Interfaces\Repositories\AssistanceRequestRepositoryInterface;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Support\Collection;

class WaiterStatisticsService
{
    protected TableRepositoryInterface $tableRepository;
    protected OrderRepositoryInterface $orderRepository;
    protected AssistanceRequestRepositoryInterface $assistanceRequestRepository;

    public function __construct(
        TableRepositoryInterface $tableRepository,
        OrderRepositoryInterface $orderRepository,
        AssistanceRequestRepositoryInterface $assistanceRequestRepository
    ) {
        $this->tableRepository = $tableRepository;
        $this->orderRepository = $orderRepository;
        $this->assistanceRequestRepository = $assistanceRequestRepository;
    }

    public function getDashboardStatistics(): array
    {
        $tables = $this->tableRepository->getActiveTablesWithSessions();

        $occupiedTables = 0;
        $sessionIds = [];

        foreach ($tables as $table) {
            $session = $table->activeSession;

            if ($session && in_array($session->status, ['open', 'billing'])) {
                $occupiedTables++;
                $sessionIds[] = $session->id;
            }
        }

        $orders = $sessionIds
            ? $this->orderRepository->getOrdersBySessionIds($sessionIds)
            : collect();

        $pendingOrders = $this->countOrdersByStatus($orders, 'pending');
        $readyOrders = $this->countOrdersByStatus($orders, 'ready');
        $completedToday = $this->countCompletedToday($orders);

        $assistanceRequests = $this->assistanceRequestRepository->getOpenRequests()->count();

        return [
            'active_tables' => $occupiedTables,
            'pending_orders' => $pendingOrders,
            'ready_orders' => $readyOrders,
            'completed_today' => $completedToday,
            'assistance_requests' => $assistanceRequests,
        ];
    }

    private function countOrdersByStatus(Collection $orders, string $status): int
    {
        return $orders->where('status', $status)->count();
    }

    private function countCompletedToday(Collection $orders): int
    {
        $today = now()->startOfDay();

        return $orders->filter(function (Order $order) use ($today) {
            $readyAt = $order->ready_at ?? $order->fully_served_at;

            return $readyAt && $readyAt->greaterThanOrEqualTo($today);
        })->count();
    }
}
