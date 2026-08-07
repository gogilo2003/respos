<?php

namespace App\Domain\Billing\Enums;

enum BillStatus: string
{
    case Draft = 'draft';
    case Open = 'open';
    case Paid = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Cancelled = 'cancelled';
    case Voided = 'voided';
}
