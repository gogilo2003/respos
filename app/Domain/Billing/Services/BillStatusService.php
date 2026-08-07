<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Exceptions\InvalidBillTransitionException;

final readonly class BillStatusService
{
    public function transition(BillStatus $from, BillStatus $to): BillStatus
    {
        if (! $this->isAllowed($from, $to)) {
            throw InvalidBillTransitionException::from($from, $to);
        }

        return $to;
    }

    private function isAllowed(BillStatus $from, BillStatus $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return match ($from) {
            BillStatus::Draft => $to === BillStatus::Open,
            BillStatus::Open => $to === BillStatus::Paid || $to === BillStatus::Voided,
            BillStatus::Paid => $to === BillStatus::Refunded,
            default => false,
        };
    }
}
