<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\Services\BillNumberGenerator;
use App\Repositories\Contracts\BillRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class BillNumberGeneratorTest extends TestCase
{
    public function test_it_generates_first_number_with_defaults(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('nextBillNumber')->with('BILL', 2026)->willReturn('BILL-2026-000001');

        $generator = new BillNumberGenerator($bills);

        $this->assertSame('BILL-2026-000001', $generator->next());
    }

    public function test_it_supports_custom_prefix(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('nextBillNumber')->with('INV', 2026)->willReturn('INV-2026-000001');

        $generator = new BillNumberGenerator($bills, 'INV');

        $this->assertSame('INV-2026-000001', $generator->next());
    }

    public function test_it_supports_custom_year(): void
    {
        $bills = $this->createMock(BillRepositoryInterface::class);
        $bills->method('nextBillNumber')->with('BILL', 2025)->willReturn('BILL-2025-000042');

        $generator = new BillNumberGenerator($bills, 'BILL', 2025);

        $this->assertSame('BILL-2025-000042', $generator->next());
    }
}
