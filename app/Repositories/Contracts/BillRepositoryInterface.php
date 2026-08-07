<?php

namespace App\Repositories\Contracts;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use Illuminate\Database\Eloquent\Collection;

interface BillRepositoryInterface
{
    public function create(BillData $bill): BillData;

    public function update(BillData $bill): BillData;

    public function save(BillData $bill): BillData;

    public function delete(int $billId): bool;

    public function find(int $billId): ?BillData;

    public function findByNumber(string $billNumber): ?BillData;

    public function findByOrder(int $orderId): ?BillData;

    public function findByStatus(BillStatus $status): ?BillData;

    public function nextBillNumber(string $prefix, int $year): string;

    /**
     * @return Collection<int, BillData>
     */
    public function findOpenBills(): Collection;

    /**
     * @return Collection<int, BillData>
     */
    public function findPaidBills(): Collection;

    public function existsForOrder(int $orderId): bool;
}
