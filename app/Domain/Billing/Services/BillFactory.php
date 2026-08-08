<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Models\Order;

final readonly class BillFactory
{
    public static function fromOrder(Order $order): BillData
    {
        $session = $order->session;
        $table = $session?->table;

        $items = $order->items->map(function ($item) {
            $menuItem = $item->menuItem;

            return [
                'name' => $menuItem?->name ?? 'Unknown Item',
                'quantity' => $item->quantity,
                'unit_price' => (float) ($item->unit_price ?? $menuItem?->base_price ?? 0),
                'line_total' => (float) ($item->unit_price ?? $menuItem?->base_price ?? 0) * $item->quantity,
            ];
        })->all();

        return BillData::from(
            billNumber: self::placeholderBillNumber($order),
            customer: $table?->customer ?? null,
            table: $table?->table_number ?? null,
            order: 'Order #'.$order->id,
            items: $items,
            subtotal: 0,
            discount: 0,
            tax: 0,
            serviceCharge: 0,
            grandTotal: 0,
            status: BillStatus::Draft,
            createdAt: new \DateTimeImmutable,
            sessionId: $order->session_id,
            generatedBy: null,
            discountApprovedBy: null,
            discountReason: null,
            voidedBy: null,
            voidReason: null,
            paidAt: null,
            voidedAt: null,
        );
    }

    private static function placeholderBillNumber(Order $order): string
    {
        return 'BILL-ORDER-'.$order->id.'-DRAFT';
    }
}
