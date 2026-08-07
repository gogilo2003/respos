<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillGeneratorInterface;
use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Services\BillFactory;
use App\Models\Order;

final readonly class BillGenerator implements BillGeneratorInterface
{
    public function __construct(private BillNumberGenerator $numbers)
    {
    }

    public function generateForOrder(Order $order): BillData
    {
        $bill = BillFactory::fromOrder($order);

        return BillData::from(
            billNumber: $this->numbers->next(),
            customer: $bill->customer,
            table: $bill->table,
            order: $bill->order,
            items: $bill->items(),
            subtotal: $bill->subtotal->amountInCents() / 100,
            discount: $bill->discount->amountInCents() / 100,
            tax: $bill->tax->amountInCents() / 100,
            serviceCharge: $bill->serviceCharge->amountInCents() / 100,
            grandTotal: $bill->grandTotal->amountInCents() / 100,
            status: $bill->status,
            createdAt: $bill->createdAt,
            sessionId: $bill->sessionId,
            generatedBy: auth()->id() ?? $order->placed_by_user,
            discountApprovedBy: $bill->discountApprovedBy,
            discountReason: $bill->discountReason,
            voidedBy: $bill->voidedBy,
            voidReason: $bill->voidReason,
            paidAt: $bill->paidAt,
            voidedAt: $bill->voidedAt,
        );
    }
}
