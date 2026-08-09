<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\DTOs\BillItemData;
use App\Models\OrderItem;

final readonly class BillItemFactory
{
    public static function fromOrderItem(OrderItem $orderItem): BillItemData
    {
        $menuItem = $orderItem->menuItem;

        return BillItemData::from(
            menuItem: $menuItem?->name ?? 'Unknown Item',
            quantity: $orderItem->quantity,
            unitPrice: (float) ($orderItem->unit_price ?? 0),
            discount: 0,
            tax: 0,
            lineTotal: 0,
            specialNotes: $orderItem->special_instructions,
        );
    }
}
