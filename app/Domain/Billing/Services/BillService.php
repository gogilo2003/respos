<?php

namespace App\Domain\Billing\Services;

use App\Domain\Billing\Contracts\BillGeneratorInterface;
use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Events\BillClosed;
use App\Domain\Billing\Events\BillCreated;
use App\Domain\Billing\Events\BillOpened;
use App\Domain\Billing\Events\BillVoided;
use App\Domain\Billing\Exceptions\BillAlreadyPaidException;
use App\Domain\Billing\Exceptions\BillVoidedException;
use App\Domain\Billing\Exceptions\InvalidPaymentException;
use App\Models\Order;
use App\Repositories\Contracts\BillRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

final readonly class BillService
{
    public function __construct(
        private BillRepositoryInterface $bills,
        private BillGeneratorInterface $generator,
    ) {
    }

    public function generate(BillData $bill): BillData
    {
        $bill = $this->bills->create($bill);

        Log::info('Bill created', [
            'bill_number' => $bill->billNumber,
            'status' => $bill->status->value,
            'session_id' => $bill->sessionId,
        ]);

        event(new BillCreated($bill));

        return $bill;
    }

    public function generateFromOrder(Order $order): BillData
    {
        $bill = $this->generator->generateForOrder($order);
        $bill = $this->bills->create($bill);

        Log::info('Bill generated from order', [
            'bill_number' => $bill->billNumber,
            'status' => $bill->status->value,
            'order_id' => $order->id,
            'session_id' => $order->session_id,
        ]);

        event(new BillCreated($bill));

        return $bill;
    }

    public function update(BillData $bill): BillData
    {
        if ($bill->status === BillStatus::Paid) {
            throw new BillAlreadyPaidException('Bill is already paid.');
        }

        if ($bill->status === BillStatus::Voided) {
            throw new BillVoidedException('Bill is already voided.');
        }

        return $this->bills->update($bill);
    }

    public function open(int $billId): BillData
    {
        $bill = $this->bills->find($billId);

        if (! $bill) {
            throw new InvalidPaymentException('Bill not found.');
        }

        if ($bill->status === BillStatus::Paid) {
            throw new BillAlreadyPaidException('Bill is already paid.');
        }

        if ($bill->status === BillStatus::Voided) {
            throw new BillVoidedException('Bill is already voided.');
        }

        $previousStatus = $bill->status;

        $bill = $this->bills->update(BillData::from(
            billNumber: $bill->billNumber,
            customer: $bill->customer,
            table: $bill->table,
            order: $bill->order,
            items: $bill->items(),
            subtotal: $bill->subtotal->amountInCents() / 100,
            discount: $bill->discount->amountInCents() / 100,
            tax: $bill->tax->amountInCents() / 100,
            serviceCharge: $bill->serviceCharge->amountInCents() / 100,
            grandTotal: $bill->grandTotal->amountInCents() / 100,
            status: BillStatus::Open,
            createdAt: $bill->createdAt,
            sessionId: $bill->sessionId,
            generatedBy: $bill->generatedBy,
            discountApprovedBy: $bill->discountApprovedBy,
            discountReason: $bill->discountReason,
            voidedBy: $bill->voidedBy,
            voidReason: $bill->voidReason,
            paidAt: $bill->paidAt,
            voidedAt: $bill->voidedAt,
        ));

        Log::info('Bill state changed', [
            'bill_number' => $bill->billNumber,
            'previous_status' => $previousStatus->value,
            'new_status' => $bill->status->value,
        ]);

        event(new BillOpened($bill));

        return $bill;
    }

    public function close(int $billId): BillData
    {
        $bill = $this->bills->find($billId);

        if (! $bill) {
            throw new InvalidPaymentException('Bill not found.');
        }

        if ($bill->status === BillStatus::Voided) {
            throw new BillVoidedException('Bill is already voided.');
        }

        $previousStatus = $bill->status;

        $bill = $this->bills->update(BillData::from(
            billNumber: $bill->billNumber,
            customer: $bill->customer,
            table: $bill->table,
            order: $bill->order,
            items: $bill->items(),
            subtotal: $bill->subtotal->amountInCents() / 100,
            discount: $bill->discount->amountInCents() / 100,
            tax: $bill->tax->amountInCents() / 100,
            serviceCharge: $bill->serviceCharge->amountInCents() / 100,
            grandTotal: $bill->grandTotal->amountInCents() / 100,
            status: BillStatus::Paid,
            createdAt: $bill->createdAt,
            sessionId: $bill->sessionId,
            generatedBy: $bill->generatedBy,
            discountApprovedBy: $bill->discountApprovedBy,
            discountReason: $bill->discountReason,
            voidedBy: $bill->voidedBy,
            voidReason: $bill->voidReason,
            paidAt: new \DateTimeImmutable(),
            voidedAt: $bill->voidedAt,
        ));

        Log::info('Bill state changed', [
            'bill_number' => $bill->billNumber,
            'previous_status' => $previousStatus->value,
            'new_status' => $bill->status->value,
        ]);

        event(new BillClosed($bill));

        return $bill;
    }

    public function void(int $billId, ?string $reason = null, ?int $voidedBy = null): BillData
    {
        $bill = $this->bills->find($billId);

        if (! $bill) {
            throw new InvalidPaymentException('Bill not found.');
        }

        if ($bill->status === BillStatus::Paid) {
            throw new BillAlreadyPaidException('Bill is already paid.');
        }

        if ($bill->status === BillStatus::Voided) {
            throw new BillVoidedException('Bill is already voided.');
        }

        $previousStatus = $bill->status;

        $bill = $this->bills->update(BillData::from(
            billNumber: $bill->billNumber,
            customer: $bill->customer,
            table: $bill->table,
            order: $bill->order,
            items: $bill->items(),
            subtotal: $bill->subtotal->amountInCents() / 100,
            discount: $bill->discount->amountInCents() / 100,
            tax: $bill->tax->amountInCents() / 100,
            serviceCharge: $bill->serviceCharge->amountInCents() / 100,
            grandTotal: $bill->grandTotal->amountInCents() / 100,
            status: BillStatus::Voided,
            createdAt: $bill->createdAt,
            sessionId: $bill->sessionId,
            generatedBy: $bill->generatedBy,
            discountApprovedBy: $bill->discountApprovedBy,
            discountReason: $bill->discountReason,
            voidedBy: $voidedBy,
            voidReason: $reason,
            paidAt: $bill->paidAt,
            voidedAt: new \DateTimeImmutable(),
        ));

        Log::info('Bill voided', [
            'bill_number' => $bill->billNumber,
            'previous_status' => $previousStatus->value,
            'new_status' => $bill->status->value,
            'reason' => $reason,
            'voided_by' => $voidedBy,
        ]);

        event(new BillVoided($bill));

        return $bill;
    }

    public function retrieve(int $billId): ?BillData
    {
        return $this->bills->find($billId);
    }

    /**
     * @return Collection<int, BillData>
     */
    public function all(): Collection
    {
        return $this->bills->findAll(\App\Domain\Billing\DTOs\BillFilter::from());
    }

    public function delete(int $billId): bool
    {
        return $this->bills->delete($billId);
    }

    private function toBillData(Bill $bill): BillData
    {
        return BillData::from(
            billNumber: $bill->bill_number ?? 'BILL-' . $bill->id,
            customer: $bill->session?->table?->customer ?? null,
            table: $bill->session?->table?->table_number ?? null,
            order: $bill->session?->table?->order ?? null,
            items: [],
            subtotal: (float) ($bill->subtotal ?? 0),
            discount: (float) ($bill->discount_amount ?? 0),
            tax: (float) ($bill->vat_amount ?? 0),
            serviceCharge: (float) ($bill->service_charge_amount ?? 0),
            grandTotal: (float) ($bill->grand_total ?? 0),
            status: BillStatus::from($bill->status),
            createdAt: $bill->created_at?->toDateTimeImmutable() ?? new \DateTimeImmutable(),
            sessionId: $bill->session_id,
            generatedBy: $bill->generated_by,
            discountApprovedBy: $bill->discount_approved_by,
            discountReason: $bill->discount_reason,
            voidedBy: $bill->voided_by,
            voidReason: $bill->void_reason,
            paidAt: $bill->paid_at?->toDateTimeImmutable() ?? null,
            voidedAt: $bill->voided_at?->toDateTimeImmutable() ?? null,
        );
    }
}
