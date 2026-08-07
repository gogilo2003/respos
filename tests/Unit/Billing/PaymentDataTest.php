<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\PaymentData;
use App\Domain\Billing\Enums\PaymentMethod;
use App\Domain\Billing\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentDataTest extends TestCase
{
    public function test_it_can_be_created_from_scalar_values(): void
    {
        $dto = PaymentData::from(
            paymentMethod: 'cash',
            amount: 100.00,
            reference: 'PAY-001',
            cashier: 'Admin',
            bill: 'Bill #1',
            paidAt: new \DateTimeImmutable('2026-08-07 12:00:00'),
            status: 'completed',
        );

        $this->assertSame(PaymentMethod::Cash, $dto->paymentMethod);
        $this->assertSame(10000, $dto->amount->amountInCents());
        $this->assertSame('PAY-001', $dto->reference);
        $this->assertSame('Admin', $dto->cashier);
        $this->assertSame('Bill #1', $dto->bill);
        $this->assertSame(PaymentStatus::Completed, $dto->status);
    }

    public function test_it_accepts_enum_values_directly(): void
    {
        $dto = PaymentData::from(
            paymentMethod: PaymentMethod::Card,
            amount: 250.50,
            reference: 'CARD-123',
            cashier: 'Cashier Jane',
            bill: 'Bill #2',
            paidAt: new \DateTimeImmutable('2026-08-07 13:00:00'),
            status: PaymentStatus::Pending,
        );

        $this->assertSame(PaymentMethod::Card, $dto->paymentMethod);
        $this->assertSame(PaymentStatus::Pending, $dto->status);
    }
}
