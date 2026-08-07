<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;

interface PaymentProcessorInterface
{
    public function process(Bill $bill, float $amountReceived, int $cashierId): array;
}
