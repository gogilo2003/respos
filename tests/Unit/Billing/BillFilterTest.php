<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillFilter;
use App\Domain\Billing\Enums\BillStatus;
use PHPUnit\Framework\TestCase;

final class BillFilterTest extends TestCase
{
    public function test_it_can_be_created_with_no_filters(): void
    {
        $filter = BillFilter::from();

        $this->assertNull($filter->status);
        $this->assertNull($filter->customer);
        $this->assertNull($filter->from);
        $this->assertNull($filter->to);
        $this->assertNull($filter->table);
        $this->assertNull($filter->cashierId);
        $this->assertNull($filter->billNumber);
    }

    public function test_it_supports_all_filters(): void
    {
        $filter = BillFilter::from(
            status: BillStatus::Paid,
            customer: 'John Doe',
            from: '2026-01-01',
            to: '2026-12-31',
            table: 5,
            cashierId: 3,
            billNumber: 'BILL-2026-000001',
        );

        $this->assertSame(BillStatus::Paid, $filter->status);
        $this->assertSame('John Doe', $filter->customer);
        $this->assertSame('2026-01-01', $filter->from);
        $this->assertSame('2026-12-31', $filter->to);
        $this->assertSame(5, $filter->table);
        $this->assertSame(3, $filter->cashierId);
        $this->assertSame('BILL-2026-000001', $filter->billNumber);
    }
}
