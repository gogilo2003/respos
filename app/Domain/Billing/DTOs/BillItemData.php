<?php

namespace App\Domain\Billing\DTOs;

use App\Domain\Billing\ValueObjects\Money;
use App\Domain\Billing\ValueObjects\Quantity;

final readonly class BillItemData
{
    private function __construct(
        public string $menuItem,
        public Quantity $quantity,
        public Money $unitPrice,
        public Money $discount,
        public Money $tax,
        public Money $lineTotal,
        public ?string $specialNotes,
    ) {
    }

    public static function from(
        string $menuItem,
        int $quantity,
        float $unitPrice,
        float $discount,
        float $tax,
        float $lineTotal,
        ?string $specialNotes = null,
    ): self {
        return new self(
            $menuItem,
            Quantity::from($quantity),
            Money::from($unitPrice),
            Money::from($discount),
            Money::from($tax),
            Money::from($lineTotal),
            $specialNotes,
        );
    }
}
