<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Services\BillCollection;
use PHPUnit\Framework\TestCase;

final class BillCollectionTest extends TestCase
{
    private function createBill(string $billNumber, BillStatus $status): BillData
    {
        return BillData::from(
            billNumber: $billNumber,
            customer: 'Customer',
            table: 'A1',
            order: null,
            items: [],
            subtotal: 0,
            discount: 0,
            tax: 0,
            serviceCharge: 0,
            grandTotal: 0,
            status: $status,
            createdAt: new \DateTimeImmutable(),
            sessionId: 1,
            generatedBy: null,
            discountApprovedBy: null,
            discountReason: null,
            voidedBy: null,
            voidReason: null,
            paidAt: null,
            voidedAt: null,
        );
    }

    public function test_it_can_be_created_from_array(): void
    {
        $bills = [
            $this->createBill('BILL-1', BillStatus::Open),
            $this->createBill('BILL-2', BillStatus::Paid),
        ];

        $collection = BillCollection::from($bills);

        $this->assertCount(2, $collection);
    }

    public function test_it_supports_iteration(): void
    {
        $bills = [
            $this->createBill('BILL-1', BillStatus::Open),
            $this->createBill('BILL-2', BillStatus::Paid),
        ];

        $collection = BillCollection::from($bills);
        $numbers = [];

        foreach ($collection as $bill) {
            $numbers[] = $bill->billNumber;
        }

        $this->assertSame(['BILL-1', 'BILL-2'], $numbers);
    }

    public function test_it_supports_count(): void
    {
        $collection = BillCollection::from([
            $this->createBill('BILL-1', BillStatus::Open),
            $this->createBill('BILL-2', BillStatus::Paid),
        ]);

        $this->assertSame(2, $collection->count());
    }

    public function test_it_supports_filter(): void
    {
        $collection = BillCollection::from([
            $this->createBill('BILL-1', BillStatus::Open),
            $this->createBill('BILL-2', BillStatus::Paid),
            $this->createBill('BILL-3', BillStatus::Open),
        ]);

        $openBills = $collection->filter(fn (BillData $bill) => $bill->status === BillStatus::Open);

        $this->assertCount(2, $openBills);
        $this->assertSame('BILL-1', $openBills->all()[0]->billNumber);
        $this->assertSame('BILL-3', $openBills->all()[1]->billNumber);
    }

    public function test_it_supports_map(): void
    {
        $collection = BillCollection::from([
            $this->createBill('BILL-1', BillStatus::Open),
            $this->createBill('BILL-2', BillStatus::Paid),
        ]);

        $mapped = $collection->map(fn (BillData $bill) => BillData::from(
            billNumber: $bill->billNumber . '-MAPPED',
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
            generatedBy: $bill->generatedBy,
            discountApprovedBy: $bill->discountApprovedBy,
            discountReason: $bill->discountReason,
            voidedBy: $bill->voidedBy,
            voidReason: $bill->voidReason,
            paidAt: $bill->paidAt,
            voidedAt: $bill->voidedAt,
        ));

        $this->assertCount(2, $mapped);
        $this->assertSame('BILL-1-MAPPED', $mapped->all()[0]->billNumber);
        $this->assertSame('BILL-2-MAPPED', $mapped->all()[1]->billNumber);
    }

    public function test_it_is_immutable(): void
    {
        $bills = [
            $this->createBill('BILL-1', BillStatus::Open),
        ];

        $collection = BillCollection::from($bills);
        $filtered = $collection->filter(fn (BillData $bill) => false);

        $this->assertCount(1, $collection);
        $this->assertCount(0, $filtered);
    }
}
