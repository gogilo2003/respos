<?php

namespace App\Repositories;

use App\Interfaces\Repositories\BillRepositoryInterface;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\BillSplit;
use Illuminate\Database\Eloquent\Collection;

class BillRepository extends BaseRepository implements BillRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Bill());
    }

    public function findBySessionId(int $sessionId): ?Bill
    {
        return $this->model->where('session_id', $sessionId)->first();
    }

    public function findWithItems(int $billId): ?Bill
    {
        return $this->model->with('items.orderItem.menuItem', 'splits.items.billItem.orderItem.menuItem')
            ->find($billId);
    }

    public function getOpenBills(): Collection
    {
        return $this->model->open()->get();
    }

    public function getDraftBills(): Collection
    {
        return $this->model->where('status', 'draft')->get();
    }

    public function createBill(array $data): Bill
    {
        return $this->model->create($data);
    }

    public function updateBill(Bill $bill, array $data): Bill
    {
        $bill->update($data);
        return $bill->fresh();
    }

    public function addBillItem(Bill $bill, array $data): BillItem
    {
        return $bill->items()->create($data);
    }

    public function createSplit(Bill $bill, array $data): BillSplit
    {
        return $bill->splits()->create($data);
    }
}