<?php

namespace App\Domain\Billing\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case FixedAmount = 'fixed_amount';
}
