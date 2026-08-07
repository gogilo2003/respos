<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;

interface DiscountCalculatorInterface
{
    public function calculate(Bill $bill, ?float $amount, ?string $reason, ?int $approvedBy): void;
}
