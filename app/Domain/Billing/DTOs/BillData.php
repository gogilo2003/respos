<?php

namespace App\Domain\Billing\DTOs;

use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\ValueObjects\Money;

final readonly class BillData
{
    /**
     * @param array<int, array{
     *     name: string,
     *     quantity: int,
     *     unit_price: float,
     *     line_total: float,
     * }> $items
     */
    private function __construct(
        public string $billNumber,
        public ?string $customer,
        public ?string $table,
        public ?string $order,
        public array $items,
        public Money $subtotal,
        public Money $discount,
        public Money $tax,
        public Money $serviceCharge,
        public Money $grandTotal,
        public BillStatus $status,
        public ?int $sessionId,
        public ?int $generatedBy,
        public ?int $discountApprovedBy,
        public ?string $discountReason,
        public ?int $voidedBy,
        public ?string $voidReason,
        public \DateTimeImmutable $createdAt,
        public ?\DateTimeImmutable $paidAt,
        public ?\DateTimeImmutable $voidedAt,
    ) {}

    /**
     * @param array<int, array{
     *     name: string,
     *     quantity: int,
     *     unit_price: float,
     *     line_total: float,
     * }> $items
     */
    public static function from(
        string $billNumber,
        ?string $customer,
        ?string $table,
        ?string $order,
        array $items,
        float $subtotal,
        float $discount,
        float $tax,
        float $serviceCharge,
        float $grandTotal,
        BillStatus $status,
        ?int $sessionId,
        ?int $generatedBy,
        ?int $discountApprovedBy,
        ?string $discountReason,
        ?int $voidedBy,
        ?string $voidReason,
        \DateTimeImmutable $createdAt,
        ?\DateTimeImmutable $paidAt,
        ?\DateTimeImmutable $voidedAt,
    ): self {
        return new self(
            $billNumber,
            $customer,
            $table,
            $order,
            $items,
            Money::from($subtotal),
            Money::from($discount),
            Money::from($tax),
            Money::from($serviceCharge),
            Money::from($grandTotal),
            $status,
            $sessionId,
            $generatedBy,
            $discountApprovedBy,
            $discountReason,
            $voidedBy,
            $voidReason,
            $createdAt,
            $paidAt,
            $voidedAt,
        );
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     quantity: int,
     *     unit_price: float,
     *     line_total: float,
     * }>
     */
    public function items(): array
    {
        return $this->items;
    }
}
