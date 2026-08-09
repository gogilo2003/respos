<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;

interface ServiceChargeCalculatorInterface
{
    public function calculate(Bill $bill): void;
}
