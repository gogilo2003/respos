<?php

namespace App\Domain\Billing\ValueObjects;

final readonly class Money
{
    /**
     * @param  int  $amountInCents  Amount stored in minor currency units (cents).
     */
    private function __construct(private int $amountInCents) {}

    /**
     * Create from a decimal string or float, converting to cents safely.
     */
    public static function from(string|float|int $amount): self
    {
        $normalized = (string) $amount;
        $sign = 1;

        if (str_starts_with($normalized, '-')) {
            $sign = -1;
            $normalized = substr($normalized, 1);
        }

        if (! str_contains($normalized, '.')) {
            $cents = (int) $normalized * 100;
        } else {
            $parts = explode('.', $normalized, 2);
            $dollars = (int) $parts[0];
            $fraction = str_pad($parts[1], 2, '0');
            $fraction = substr($fraction, 0, 2);

            $cents = ($dollars * 100) + (int) $fraction;
        }

        return new self($sign * $cents);
    }

    /**
     * Create directly from minor currency units.
     */
    public static function fromCents(int $amountInCents): self
    {
        return new self($amountInCents);
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function add(self $other): self
    {
        return new self($this->amountInCents + $other->amountInCents);
    }

    public function subtract(self $other): self
    {
        return new self($this->amountInCents - $other->amountInCents);
    }

    /**
     * Multiply by a scalar and round to nearest cent.
     */
    public function multiply(float|int $multiplier): self
    {
        $normalized = (string) $multiplier;

        if (! str_contains($normalized, '.')) {
            $result = $this->amountInCents * (int) $normalized;
        } else {
            $parts = explode('.', $normalized, 2);
            $whole = (int) $parts[0];
            $fraction = str_pad($parts[1], 2, '0');
            $fraction = substr($fraction, 0, 2);
            $scaled = (int) ($parts[0].$fraction);

            $result = (int) round(($this->amountInCents * $scaled) / 100);
        }

        return new self($result);
    }

    /**
     * Divide by a scalar and round to nearest cent.
     */
    public function divide(float|int $divisor): self
    {
        if ($divisor === 0) {
            throw new \InvalidArgumentException('Divisor cannot be zero.');
        }

        $normalized = (string) $divisor;

        if (! str_contains($normalized, '.')) {
            $result = (int) round($this->amountInCents / (int) $normalized);
        } else {
            $parts = explode('.', $normalized, 2);
            $fraction = str_pad($parts[1], 2, '0');
            $fraction = substr($fraction, 0, 2);
            $scaled = (int) ($parts[0].$fraction);

            $result = (int) round(($this->amountInCents * 100) / $scaled);
        }

        return new self($result);
    }

    public function compare(self $other): int
    {
        return $this->amountInCents <=> $other->amountInCents;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->compare($other) > 0;
    }

    public function isLessThan(self $other): bool
    {
        return $this->compare($other) < 0;
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents;
    }

    public function format(string $currency = 'USD'): string
    {
        $sign = $this->amountInCents < 0 ? '-' : '';
        $abs = abs($this->amountInCents);

        $dollars = (int) floor($abs / 100);
        $cents = $abs % 100;

        return sprintf('%s%d.%02d %s', $sign, $dollars, $cents, strtoupper($currency));
    }

    /**
     * @return array{amount: int, currency: string}
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amountInCents,
            'currency' => 'USD',
        ];
    }
}
