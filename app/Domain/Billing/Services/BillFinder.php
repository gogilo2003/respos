<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Repositories\Contracts\BillRepositoryInterface;

final readonly class BillFinder
{
    public function __construct(private BillRepositoryInterface $bills)
    {
    }

    public function findById(int $billId): ?BillData
    {
        return $this->bills->find($billId);
    }

    public function findByNumber(string $billNumber): ?BillData
    {
        return $this->bills->findByNumber($billNumber);
    }

    public function findByOrder(int $orderId): ?BillData
    {
        return $this->bills->findByOrder($orderId);
    }

    public function findByStatus(BillStatus $status): ?BillData
    {
        return $this->bills->findByStatus($status);
    }
}
