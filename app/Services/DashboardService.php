<?php

namespace App\Services;

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
                'props' => [
                    'statistics' => $this->waiterStatisticsService->getDashboardStatistics(),
                ],
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
}
