<?php

namespace App\Interfaces\Repositories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Collection;

interface BillRepositoryInterface extends RepositoryInterface
{
    public function findBySessionId(int $sessionId): ?Bill;

    public function findWithItems(int $billId): ?Bill;

    public function getOpenBills(): Collection;

    public function getDraftBills(): Collection;

    public function createBill(array $data): Bill;

    public function updateBill(Bill $bill, array $data): Bill;

    public function addBillItem(Bill $bill, array $data): BillItem;

    public function createSplit(Bill $bill, array $data): BillSplit;
}