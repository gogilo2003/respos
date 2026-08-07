<?php

namespace App\Domain\Billing\DTOs;

use App\Domain\Billing\Enums\PaymentMethod;
use App\Domain\Billing\Enums\PaymentStatus;
use App\Domain\Billing\ValueObjects\Money;

final readonly class PaymentData
{
    private function __construct(
        public PaymentMethod $paymentMethod,
        public Money $amount,
        public string $reference,
        public string $cashier,
        public string $bill,
        public \DateTimeImmutable $paidAt,
        public PaymentStatus $status,
    ) {
    }

    public static function from(
        PaymentMethod|string $paymentMethod,
        float $amount,
        string $reference,
        string $cashier,
        string $bill,
        \DateTimeImmutable $paidAt,
        PaymentStatus|string $status,
    ): self {
        return new self(
            $paymentMethod instanceof PaymentMethod ? $paymentMethod : PaymentMethod::from($paymentMethod),
            Money::from($amount),
            $reference,
            $cashier,
            $bill,
            $paidAt,
            $status instanceof PaymentStatus ? $status : PaymentStatus::from($status),
        );
    }
}
