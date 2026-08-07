<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\DTOs\BillFilter;
use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Services\BillFinder;
use App\Repositories\Contracts\BillRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\TestCase;

final class BillFinderTest extends TestCase
{
    public function test_it_finds_by_id(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('find')->with(1)->willReturn(BillData::from(
            billNumber: 'BILL-2026-000001',
            customer: 'John Doe',
            table: 'A1',
            order: null,
            items: [],
            subtotal: 0,
            discount: 0,
            tax: 0,
            serviceCharge: 0,
            grandTotal: 0,
            status: BillStatus::Open,
            createdAt: new \DateTimeImmutable(),
            sessionId: 1,
            generatedBy: null,
            discountApprovedBy: null,
            discountReason: null,
            voidedBy: null,
            voidReason: null,
            paidAt: null,
            voidedAt: null,
        ));

        $finder = new BillFinder($bills);
        $bill = $finder->findById(1);

        $this->assertNotNull($bill);
        $this->assertSame('BILL-2026-000001', $bill->billNumber);
    }

    public function test_it_finds_by_number(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('findByNumber')->with('BILL-2026-000001')->willReturn(BillData::from(
            billNumber: 'BILL-2026-000001',
            customer: 'Jane Doe',
            table: 'B2',
            order: null,
            items: [],
            subtotal: 0,
            discount: 0,
            tax: 0,
            serviceCharge: 0,
            grandTotal: 0,
            status: BillStatus::Paid,
            createdAt: new \DateTimeImmutable(),
            sessionId: 2,
            generatedBy: null,
            discountApprovedBy: null,
            discountReason: null,
            voidedBy: null,
            voidReason: null,
            paidAt: new \DateTimeImmutable(),
            voidedAt: null,
        ));

        $finder = new BillFinder($bills);
        $bill = $finder->findByNumber('BILL-2026-000001');

        $this->assertNotNull($bill);
        $this->assertSame('Jane Doe', $bill->customer);
    }

    public function test_it_finds_by_order(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('findByOrder')->with(5)->willReturn(null);

        $finder = new BillFinder($bills);
        $bill = $finder->findByOrder(5);

        $this->assertNull($bill);
    }

    public function test_it_finds_by_status(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('findByStatus')->with(BillStatus::Voided)->willReturn(null);

        $finder = new BillFinder($bills);
        $bill = $finder->findByStatus(BillStatus::Voided);

        $this->assertNull($bill);
    }

    public function test_it_finds_using_filter(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('findAll')->with($this->anything())->willReturn(new Collection([
            BillData::from(
                billNumber: 'BILL-2026-000001',
                customer: 'John Doe',
                table: 'A1',
                order: null,
                items: [],
                subtotal: 0,
                discount: 0,
                tax: 0,
                serviceCharge: 0,
                grandTotal: 0,
                status: BillStatus::Paid,
                createdAt: new \DateTimeImmutable(),
                sessionId: 1,
                generatedBy: 3,
                discountApprovedBy: null,
                discountReason: null,
                voidedBy: null,
                voidReason: null,
                paidAt: new \DateTimeImmutable(),
                voidedAt: null,
            ),
        ]));

        $finder = new BillFinder($bills);
        $filter = BillFilter::from(
            status: BillStatus::Paid,
            cashierId: 3,
        );
        $result = $finder->findAll($filter);

        $this->assertCount(1, $result);
        $this->assertSame('BILL-2026-000001', $result->first()->billNumber);
    }
}

