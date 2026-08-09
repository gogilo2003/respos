<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;

interface TaxCalculatorInterface
{
    public function calculate(Bill $bill): void;
}
