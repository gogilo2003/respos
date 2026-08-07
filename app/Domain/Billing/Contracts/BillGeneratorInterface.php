<?php

namespace App\Domain\Billing\Contracts;

use App\Domain\Billing\DTOs\BillData;
use App\Models\Order;

interface BillGeneratorInterface
{
    public function generateForOrder(Order $order): BillData;
}
