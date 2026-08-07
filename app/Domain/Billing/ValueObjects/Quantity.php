<?php

namespace App\Domain\Billing\ValueObjects;

final readonly class Quantity
{
    private function __construct(private int $value)
    {
    }

    public static function from(int $value): self
    {
        self::validate($value);

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return new self($this->value + $other->value);
    }

    public function subtract(self $other): self
    {
        $result = $this->value - $other->value;
        self::validate($result);

        return new self($result);
    }

    public function multiply(int $multiplier): self
    {
        if ($multiplier < 0) {
            throw new \InvalidArgumentException('Multiplier must be non-negative.');
        }

        return new self($this->value * $multiplier);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    private static function validate(int $value): void
    {
        if ($value < 1) {
            throw new \InvalidArgumentException('Quantity must be at least 1.');
        }
    }
}
