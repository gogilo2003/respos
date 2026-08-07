<?php

namespace App\Repositories;

use App\Domain\Billing\DTOs\BillData;
use App\Models\Bill;
use Illuminate\Database\Eloquent\Collection;

class BillRepository extends BaseRepository implements \App\Repositories\Contracts\BillRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(new Bill());
    }

    public function create(BillData $bill): BillData
    {
        $model = $this->model->create($this->toEloquentArray($bill));

        return $this->toDto($model);
    }

    public function update(BillData $bill): BillData
    {
        $model = $this->find($bill->billNumber);

        if (! $model) {
            throw new \InvalidArgumentException('Bill not found for update.');
        }

        $model->update($this->toEloquentArray($bill));

        return $this->toDto($model->fresh());
    }

    public function save(BillData $bill): BillData
    {
        if ($this->find($bill->billNumber)) {
            return $this->update($bill);
        }

        return $this->create($bill);
    }

    public function delete(int $billId): bool
    {
        $model = $this->find($billId);

        if (! $model) {
            return false;
        }

        return $model->delete();
    }

    public function find(int $billId): ?BillData
    {
        $model = $this->model->find($billId);

        if (! $model) {
            return null;
        }

        return $this->toDto($model);
    }

    public function findByNumber(string $billNumber): ?BillData
    {
        $model = $this->model->find((int) $billNumber);

        if (! $model) {
            return null;
        }

        return $this->toDto($model);
    }

    public function findByOrder(int $orderId): ?BillData
    {
        $model = $this->model->whereHas('items', function ($query) use ($orderId) {
            $query->where('order_item_id', $orderId);
        })->first();

        if (! $model) {
            return null;
        }

        return $this->toDto($model);
    }

    public function findByStatus(\App\Domain\Billing\Enums\BillStatus $status): ?BillData
    {
        $model = $this->model->where('status', $status->value)->first();

        if (! $model) {
            return null;
        }

        return $this->toDto($model);
    }

    /**
     * @return Collection<int, BillData>
     */
    public function findOpenBills(): Collection
    {
        return $this->model->whereIn('status', ['open', 'partially_paid'])->get()->map(fn (Bill $bill) => $this->toDto($bill));
    }

    /**
     * @return Collection<int, BillData>
     */
    public function findPaidBills(): Collection
    {
        return $this->model->where('status', 'paid')->get()->map(fn (Bill $bill) => $this->toDto($bill));
    }

    public function existsForOrder(int $orderId): bool
    {
        return $this->model->whereHas('items', function ($query) use ($orderId) {
            $query->where('order_item_id', $orderId);
        })->exists();
    }

    public function nextBillNumber(string $prefix, int $year): string
    {
        $lastNumber = $this->model
            ->where('bill_number', 'like', "{$prefix}-{$year}-%")
            ->orderByDesc('bill_number')
            ->value('bill_number');

        if (! $lastNumber) {
            return sprintf('%s-%d-%06d', $prefix, $year, 1);
        }

        $parts = explode('-', $lastNumber);
        $sequence = (int) end($parts);

        return sprintf('%s-%d-%06d', $prefix, $year, $sequence + 1);
    }

    private function toEloquentArray(BillData $dto): array
    {
        return [
            'bill_number' => $dto->billNumber,
            'session_id' => $dto->sessionId,
            'generated_by' => $dto->generatedBy,
            'status' => $dto->status->value,
            'subtotal' => $dto->subtotal->amountInCents() / 100,
            'discount_amount' => $dto->discount->amountInCents() / 100,
            'discount_reason' => $dto->discountReason,
            'discount_approved_by' => $dto->discountApprovedBy,
            'grand_total' => $dto->grandTotal->amountInCents() / 100,
            'paid_at' => $dto->paidAt,
            'voided_at' => $dto->voidedAt,
            'voided_by' => $dto->voidedBy,
            'void_reason' => $dto->voidReason,
        ];
    }

    private function toDto(Bill $model): BillData
    {
        $customer = $model->session?->table?->customer ?? null;
        $table = $model->session?->table?->table_number ?? null;
        $order = $model->session?->table?->order ?? null;

        return BillData::from(
            billNumber: $model->bill_number ?? 'BILL-' . $model->id,
            customer: $customer,
            table: $table,
            order: $order,
            items: [],
            subtotal: (float) ($model->subtotal ?? 0),
            discount: (float) ($model->discount_amount ?? 0),
            tax: (float) ($model->vat_amount ?? 0),
            serviceCharge: (float) ($model->service_charge_amount ?? 0),
            grandTotal: (float) ($model->grand_total ?? 0),
            status: \App\Domain\Billing\Enums\BillStatus::from($model->status),
            createdAt: $model->created_at ? $model->created_at->toDateTimeImmutable() : new \DateTimeImmutable(),
            sessionId: $model->session_id,
            generatedBy: $model->generated_by,
            discountApprovedBy: $model->discount_approved_by,
            discountReason: $model->discount_reason,
            voidedBy: $model->voided_by,
            voidReason: $model->void_reason,
            paidAt: $model->paid_at ? $model->paid_at->toDateTimeImmutable() : null,
            voidedAt: $model->voided_at ? $model->voided_at->toDateTimeImmutable() : null,
        );
    }
}
