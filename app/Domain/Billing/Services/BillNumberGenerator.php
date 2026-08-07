<?php

namespace App\Domain\Billing\Services;

use App\Repositories\Contracts\BillRepositoryInterface;

final readonly class BillNumberGenerator
{
    private string $prefix;

    private int $year;

    public function __construct(private BillRepositoryInterface $bills, ?string $prefix = null, ?int $year = null)
    {
        $this->prefix = $prefix ?? 'BILL';
        $this->year = $year ?? (int) now()->format('Y');
    }

    public function next(): string
    {
        return $this->bills->nextBillNumber($this->prefix, $this->year);
    }
}
