<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;

interface ReceiptGeneratorInterface
{
    public function generate(Bill $bill): string;
}
