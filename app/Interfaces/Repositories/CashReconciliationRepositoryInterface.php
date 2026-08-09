<?php

namespace App\Interfaces\Repositories;

use App\Models\CashReconciliation;
use Illuminate\Support\Collection;

interface CashReconciliationRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?CashReconciliation;

    public function create(array $data): CashReconciliation;

    public function approve(int $id, int $approvedById): CashReconciliation;

    public function getSystemCashTotalForDate(string $date): float;
}
