<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\TableSession;
use App\Models\User;

class AdminDashboardService
{
    /**
     * Get system-wide dashboard statistics for Admin and Manager roles.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $today = now()->startOfDay();

        $todaySales = (float) Bill::where('status', 'paid')
            ->where('paid_at', '>=', $today)
            ->sum('grand_total');

        $todayOrdersCount = Order::where('created_at', '>=', $today)->count();

        $activeSessionsCount = TableSession::where('status', 'open')->count();

        $pendingKitchenOrdersCount = Order::whereIn('status', ['pending', 'accepted', 'preparing'])->count();

        $totalUsersCount = User::where('is_active', true)->count();

        $activeCategoriesCount = MenuCategory::where('is_active', true)->count();
        $availableItemsCount = MenuItem::where('is_available', true)->count();

        return [
            'statistics' => [
                'today_sales' => $todaySales,
                'today_orders_count' => $todayOrdersCount,
                'active_sessions_count' => $activeSessionsCount,
                'pending_kitchen_orders_count' => $pendingKitchenOrdersCount,
                'total_users_count' => $totalUsersCount,
                'active_categories_count' => $activeCategoriesCount,
                'available_items_count' => $availableItemsCount,
            ],
            'recent_orders' => Order::with(['session.table'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn ($order) => [
                    'id' => $order->id,
                    'table_number' => $order->session?->table?->table_number ?? 'N/A',
                    'status' => $order->status,
                    'placed_by_role' => $order->placed_by_role,
                    'created_at' => $order->created_at?->format('H:i'),
                ]),
        ];
    }
}
