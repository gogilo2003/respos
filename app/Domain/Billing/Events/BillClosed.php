<?php

namespace App\Domain\Billing\Events;

use App\Domain\Billing\DTOs\BillData;

final readonly class BillClosed
{
    public function __construct(public BillData $bill) {}
}
