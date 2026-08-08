<?php

namespace App\Services;

use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Interfaces\Repositories\WaiterStatisticsRepositoryInterface;

class WaiterStatisticsService
{
    protected TableRepositoryInterface $tableRepository;

    protected OrderRepositoryInterface $orderRepository;

    protected WaiterStatisticsRepositoryInterface $waiterStatisticsRepository;

    public function __construct(
        TableRepositoryInterface $tableRepository,
        OrderRepositoryInterface $orderRepository,
        WaiterStatisticsRepositoryInterface $waiterStatisticsRepository
    ) {
        $this->tableRepository = $tableRepository;
        $this->orderRepository = $orderRepository;
        $this->waiterStatisticsRepository = $waiterStatisticsRepository;
    }

    public function getDashboardStatistics(array $sessionIds = []): array
    {
        $occupiedTables = $this->waiterStatisticsRepository->getOccupiedTableCount();

        $pendingOrders = $sessionIds
            ? $this->waiterStatisticsRepository->getPendingOrderCount($sessionIds)
            : 0;

        $readyOrders = $sessionIds
            ? $this->waiterStatisticsRepository->getReadyOrderCount($sessionIds)
            : 0;

        $completedToday = $sessionIds
            ? $this->waiterStatisticsRepository->getCompletedTodayCount($sessionIds)
            : 0;

        $assistanceRequests = $this->waiterStatisticsRepository->getOpenAssistanceRequestCount();

        return [
            'active_tables' => $occupiedTables,
            'pending_orders' => $pendingOrders,
            'ready_orders' => $readyOrders,
            'completed_today' => $completedToday,
            'assistance_requests' => $assistanceRequests,
        ];
    }
}
