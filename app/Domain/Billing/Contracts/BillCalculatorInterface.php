<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;

interface BillCalculatorInterface
{
    public function calculateTotals(Bill $bill): void;
}
