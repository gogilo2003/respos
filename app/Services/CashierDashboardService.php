<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\TableSession;

class CashierDashboardService
{
    /**
     * Get billing and cash metrics for Cashier dashboard.
     *
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        $today = now()->startOfDay();

        $openBillsCount = Bill::whereIn('status', ['draft', 'open'])->count();

        $paidBillsTodayCount = Bill::where('status', 'paid')
            ->where('paid_at', '>=', $today)
            ->count();

        $cashCollectedToday = (float) Bill::where('status', 'paid')
            ->where('paid_at', '>=', $today)
            ->sum('grand_total');

        $voidedBillsTodayCount = Bill::where('status', 'voided')
            ->where('voided_at', '>=', $today)
            ->count();

        $openSessionsCount = TableSession::where('status', 'open')->count();

        $recentOpenBills = Bill::with(['session.table'])
            ->whereIn('status', ['draft', 'open'])
            ->latest('generated_at')
            ->take(6)
            ->get()
            ->map(fn ($bill) => [
                'id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'table_number' => $bill->session?->table?->table_number ?? 'N/A',
                'grand_total' => (float) $bill->grand_total,
                'status' => $bill->status,
                'created_at' => $bill->generated_at?->format('H:i'),
            ]);

        return [
            'statistics' => [
                'open_bills_count' => $openBillsCount,
                'paid_bills_today_count' => $paidBillsTodayCount,
                'cash_collected_today' => $cashCollectedToday,
                'voided_bills_today_count' => $voidedBillsTodayCount,
                'open_sessions_count' => $openSessionsCount,
            ],
            'recent_open_bills' => $recentOpenBills,
        ];
    }
}
