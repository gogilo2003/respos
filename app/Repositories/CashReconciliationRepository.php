<?php

namespace App\Repositories;

use App\Interfaces\Repositories\CashReconciliationRepositoryInterface;
use App\Models\CashReconciliation;
use App\Models\Payment;
use Illuminate\Support\Collection;

class CashReconciliationRepository implements CashReconciliationRepositoryInterface
{
    public function all(): Collection
    {
        return CashReconciliation::with(['preparedBy', 'approvedBy'])
            ->orderBy('reconciliation_date', 'desc')
            ->get();
    }

    public function find(int $id): ?CashReconciliation
    {
        return CashReconciliation::with(['preparedBy', 'approvedBy'])->find($id);
    }

    public function create(array $data): CashReconciliation
    {
        return CashReconciliation::create($data);
    }

    public function approve(int $id, int $approvedById): CashReconciliation
    {
        $reconciliation = CashReconciliation::findOrFail($id);
        $reconciliation->update([
            'approved_by' => $approvedById,
        ]);

        return $reconciliation->fresh(['preparedBy', 'approvedBy']);
    }

    public function getSystemCashTotalForDate(string $date): float
    {
        return (float) Payment::where('payment_method', 'cash')
            ->whereDate('paid_at', $date)
            ->sum('amount');
    }
}
