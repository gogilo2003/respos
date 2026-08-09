<?php

namespace App\Domain\Billing\Enums;

enum TaxType: string
{
    case Inclusive = 'inclusive';
    case Exclusive = 'exclusive';
}
