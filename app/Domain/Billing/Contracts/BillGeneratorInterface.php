<?php

namespace App\Domain\Billing\Contracts;

use App\Models\Bill;
use App\Models\TableSession;

interface BillGeneratorInterface
{
    public function generateForSession(TableSession $session, int $generatedBy): Bill;
}
