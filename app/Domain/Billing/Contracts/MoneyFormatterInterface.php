<?php

namespace App\Domain\Billing\Contracts;

interface MoneyFormatterInterface
{
    public function format(float $amount): string;
}
