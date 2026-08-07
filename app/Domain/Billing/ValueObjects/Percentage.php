<?php

namespace App\Domain\Billing\ValueObjects;

final readonly class Percentage
{
    private const MIN = 0;
    private const MAX = 100;

    private function __construct(private int $value)
    {
    }

    public static function fromDecimal(float $decimal): self
    {
        $value = (int) round($decimal * 100);

        return new self(self::clamp($value));
    }

    public static function fromPercentage(int $percentage): self
    {
        $value = (int) $percentage;

        return new self(self::clamp($value));
    }

    public function toDecimal(): float
    {
        return round($this->value / 100, 10);
    }

    public function toPercentage(): int
    {
        return $this->value;
    }

    public function apply(int|float $amount): int
    {
        return (int) round($amount * $this->toDecimal());
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function clamp(int $value): int
    {
        if ($value < self::MIN) {
            return self::MIN;
        }

        if ($value > self::MAX) {
            return self::MAX;
        }

        return $value;
    }
}
