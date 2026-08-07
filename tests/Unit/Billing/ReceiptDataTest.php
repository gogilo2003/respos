<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\ReceiptData;
use PHPUnit\Framework\TestCase;

final class ReceiptDataTest extends TestCase
{
    public function test_it_can_be_created_from_scalar_values(): void
    {
        $payments = [
            ['method' => 'cash', 'amount' => 100.00, 'reference' => 'PAY-001'],
            ['method' => 'card', 'amount' => 50.00, 'reference' => 'CARD-123'],
        ];

        $dto = ReceiptData::from(
            receiptNumber: 'RCP-001',
            bill: 'Bill #1',
            payments: $payments,
            totals: 150.00,
            tax: 12.00,
            discount: 5.00,
            printedAt: new \DateTimeImmutable('2026-08-07 12:00:00'),
        );

        $this->assertSame('RCP-001', $dto->receiptNumber);
        $this->assertSame('Bill #1', $dto->bill);
        $this->assertSame($payments, $dto->payments());
        $this->assertSame(15000, $dto->totals->amountInCents());
        $this->assertSame(1200, $dto->tax->amountInCents());
        $this->assertSame(500, $dto->discount->amountInCents());
    }

    public function test_it_accepts_empty_payments_array(): void
    {
        $dto = ReceiptData::from(
            receiptNumber: 'RCP-002',
            bill: 'Bill #2',
            payments: [],
            totals: 0.00,
            tax: 0.00,
            discount: 0.00,
            printedAt: new \DateTimeImmutable('2026-08-07 13:00:00'),
        );

        $this->assertSame([], $dto->payments());
    }
}
