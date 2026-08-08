<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    public function toArray($request): array
    {
        $bill = $this->resource;

        return [
            'bill_number' => $bill->billNumber,
            'customer' => $bill->customer,
            'table' => $bill->table,
            'order' => $bill->order,
            'items' => $bill->items(),
            'subtotal' => (float) $bill->subtotal->amountInCents() / 100,
            'discount' => (float) $bill->discount->amountInCents() / 100,
            'tax' => (float) $bill->tax->amountInCents() / 100,
            'service_charge' => (float) $bill->serviceCharge->amountInCents() / 100,
            'grand_total' => (float) $bill->grandTotal->amountInCents() / 100,
            'status' => $bill->status->value,
            'session_id' => $bill->sessionId,
            'generated_by' => $bill->generatedBy,
            'discount_approved_by' => $bill->discountApprovedBy,
            'discount_reason' => $bill->discountReason,
            'voided_by' => $bill->voidedBy,
            'void_reason' => $bill->voidReason,
            'created_at' => $bill->createdAt->format('Y-m-d H:i:s'),
            'paid_at' => $bill->paidAt?->format('Y-m-d H:i:s'),
            'voided_at' => $bill->voidedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
