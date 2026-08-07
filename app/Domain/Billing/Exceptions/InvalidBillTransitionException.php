<?php

namespace App\Domain\Billing\Exceptions;

use App\Domain\Billing\Enums\BillStatus;

class InvalidBillTransitionException extends BillingException
{
    public static function from(BillStatus $from, BillStatus $to): self
    {
        return new self("Invalid bill transition from {$from->value} to {$to->value}.");
    }
}
