<?php

namespace App\Services;

use App\Interfaces\Repositories\AssistanceRequestRepositoryInterface;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Models\User;

class DashboardService
{
    public function __construct(
        protected AdminDashboardService $adminDashboardService,
        protected CashierDashboardService $cashierDashboardService,
        protected WaiterStatisticsService $waiterStatisticsService,
        protected KitchenDashboardService $kitchenDashboardService
    ) {}

    /**
     * Determine component name and props payload for the user's role dashboard.
     *
     * @return array{redirect?: string, component?: string, props?: array<string, mixed>}
     */
    public function getDashboardPayload(?User $user): array
    {
        if (! $user) {
            return ['redirect' => route('menu')];
        }

        $role = $user->role?->name;

        return match ($role) {
            'admin', 'manager' => [
                'component' => 'Dashboard/Admin',
                'props' => $this->adminDashboardService->getDashboardData(),
            ],
            'cashier' => [
                'component' => 'Dashboard/Cashier',
                'props' => $this->cashierDashboardService->getDashboardData(),
            ],
            'waiter' => [
                'component' => 'Waiter/Dashboard',
                'props' => $this->getWaiterDashboardData(),
            ],
            'kitchen' => [
                'component' => 'Kitchen/Dashboard',
                'props' => $this->kitchenDashboardService->getDashboardData(),
            ],
            default => [
                'redirect' => route('menu'),
            ],
        };
    }

    /**
     * Build full payload required by Waiter/Dashboard.vue
     *
     * @return array<string, mixed>
     */
    protected function getWaiterDashboardData(): array
    {
        $tableRepo = app(TableRepositoryInterface::class);
        $orderRepo = app(OrderRepositoryInterface::class);
        $assistanceRepo = app(AssistanceRequestRepositoryInterface::class);

        $tables = $tableRepo->getActiveTablesWithSessions()->map(function ($table) {
            $session = $table->activeSession;

            return [
                'table_id' => $table->id,
                'table_name' => $table->table_number,
                'active_session' => $session ? [
                    'table_session_id' => $session->id,
                    'status' => $session->status,
                ] : null,
                'order_summary' => $session ? [
                    'active_orders_count' => $session->orders->count(),
                    'latest_order_status' => optional($session->orders->first())->status,
                ] : null,
                'assistance' => $session ? [
                    'open_requests_count' => $session->assistanceRequests->where('status', 'pending')->count(),
                ] : null,
            ];
        });

        $sessionIds = $tables->flatMap(fn ($table) => $table['active_session'] ? [$table['active_session']['table_session_id']] : [])->all();
        $orders = $sessionIds ? $orderRepo->getOrdersBySessionIds($sessionIds) : collect();
        $assistanceRequests = $assistanceRepo->getOpenRequests();

        $ordersPayload = $orders->map(function ($order) {
            return [
                'orderNumber' => $order->id,
                'table' => $order->session?->table?->table_number ?? 'Unknown',
                'customer' => null,
                'time' => $order->placed_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
                'status' => $order->status,
            ];
        })->values()->all();

        $assistancePayload = $assistanceRequests->map(function ($request) {
            return [
                'tableNumber' => $request->session?->table?->table_number ?? 'Unknown',
                'request' => $request->request_type ?? 'Assistance',
                'priority' => 'medium',
                'time' => $request->requested_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            ];
        })->values()->all();

        $statistics = $this->waiterStatisticsService->getDashboardStatistics($sessionIds);

        return [
            'tables' => $tables->values()->all(),
            'orders' => $ordersPayload,
            'statistics' => $statistics,
            'assistance_requests' => $assistancePayload,
        ];
    }
}
