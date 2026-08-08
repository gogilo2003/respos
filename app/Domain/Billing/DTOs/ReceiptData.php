<?php

namespace App\Domain\Billing\DTOs;

use App\Domain\Billing\ValueObjects\Money;

final readonly class ReceiptData
{
    /**
     * @param array<int, array{
     *     method: string,
     *     amount: float,
     *     reference: string,
     * }> $payments
     */
    private function __construct(
        public string $receiptNumber,
        public string $bill,
        public array $payments,
        public Money $totals,
        public Money $tax,
        public Money $discount,
        public \DateTimeImmutable $printedAt,
    ) {}

    /**
     * @param array<int, array{
     *     method: string,
     *     amount: float,
     *     reference: string,
     * }> $payments
     */
    public static function from(
        string $receiptNumber,
        string $bill,
        array $payments,
        float $totals,
        float $tax,
        float $discount,
        \DateTimeImmutable $printedAt,
    ): self {
        return new self(
            $receiptNumber,
            $bill,
            $payments,
            Money::from($totals),
            Money::from($tax),
            Money::from($discount),
            $printedAt,
        );
    }

    /**
     * @return array<int, array{
     *     method: string,
     *     amount: float,
     *     reference: string,
     * }>
     */
    public function payments(): array
    {
        return $this->payments;
    }
}
