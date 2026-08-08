<?php

namespace App\Domain\Billing\DTOs;

use App\Domain\Billing\Enums\BillStatus;

final readonly class BillFilter
{
    private function __construct(
        public ?BillStatus $status,
        public ?string $customer,
        public ?string $from,
        public ?string $to,
        public ?int $table,
        public ?int $cashierId,
        public ?string $billNumber,
    ) {}

    public static function from(
        ?BillStatus $status = null,
        ?string $customer = null,
        ?string $from = null,
        ?string $to = null,
        ?int $table = null,
        ?int $cashierId = null,
        ?string $billNumber = null,
    ): self {
        return new self(
            $status,
            $customer,
            $from,
            $to,
            $table,
            $cashierId,
            $billNumber,
        );
    }
}
