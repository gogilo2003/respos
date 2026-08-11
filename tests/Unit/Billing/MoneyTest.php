<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\ValueObjects\Money;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_it_can_be_created_from_float(): void
    {
        $money = Money::from(10.99);

        $this->assertSame(1099, $money->amountInCents());
    }

    public function test_it_can_be_created_from_int(): void
    {
        $money = Money::from(5);

        $this->assertSame(500, $money->amountInCents());
    }

    public function test_it_can_be_created_from_string(): void
    {
        $money = Money::from('12.34');

        $this->assertSame(1234, $money->amountInCents());
    }

    public function test_it_can_be_created_from_cents(): void
    {
        $money = Money::fromCents(500);

        $this->assertSame(500, $money->amountInCents());
    }

    public function test_it_supports_negative_amounts(): void
    {
        $money = Money::from('-10.50');

        $this->assertSame(-1050, $money->amountInCents());
    }

    public function test_add_returns_new_instance(): void
    {
        $a = Money::from(10.00);
        $b = Money::from(5.50);
        $sum = $a->add($b);

        $this->assertSame(1550, $sum->amountInCents());
        $this->assertSame(1000, $a->amountInCents());
        $this->assertSame(550, $b->amountInCents());
    }

    public function test_subtract_returns_new_instance(): void
    {
        $a = Money::from(20.00);
        $b = Money::from(5.25);

        $this->assertSame(1475, $a->subtract($b)->amountInCents());
    }

    public function test_multiply_by_integer(): void
    {
        $money = Money::from(10.50);

        $this->assertSame(3150, $money->multiply(3)->amountInCents());
    }

    public function test_multiply_by_float(): void
    {
        $money = Money::from(10.00);

        $this->assertSame(1500, $money->multiply(1.5)->amountInCents());
    }

    public function test_divide_by_integer(): void
    {
        $money = Money::from(15.00);

        $this->assertSame(500, $money->divide(3)->amountInCents());
    }

    public function test_divide_by_float(): void
    {
        $money = Money::from(10.00);

        $this->assertSame(333, $money->divide(3)->amountInCents());
    }

    public function test_divide_by_zero_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::from(10)->divide(0);
    }

    public function test_compare_returns_zero_when_equal(): void
    {
        $this->assertSame(0, Money::from(10)->compare(Money::from(10)));
    }

    public function test_compare_returns_positive_when_greater(): void
    {
        $this->assertGreaterThan(0, Money::from(20)->compare(Money::from(10)));
    }

    public function test_compare_returns_negative_when_less(): void
    {
        $this->assertLessThan(0, Money::from(5)->compare(Money::from(10)));
    }

    public function test_is_greater_than(): void
    {
        $this->assertTrue(Money::from(20)->isGreaterThan(Money::from(10)));
        $this->assertFalse(Money::from(5)->isGreaterThan(Money::from(10)));
    }

    public function test_is_less_than(): void
    {
        $this->assertTrue(Money::from(5)->isLessThan(Money::from(10)));
        $this->assertFalse(Money::from(20)->isLessThan(Money::from(10)));
    }

    public function test_equals_compares_amounts_only(): void
    {
        $this->assertTrue(Money::from(10)->equals(Money::from(10)));
        $this->assertFalse(Money::from(10)->equals(Money::from(20)));
    }

    public function test_format_defaults_to_kes(): void
    {
        $this->assertSame('10.99 KES', Money::from(10.99)->format());
    }

    public function test_format_uses_custom_currency(): void
    {
        $this->assertSame('10.99 USD', Money::from(10.99)->format('USD'));
    }

    public function test_format_handles_negative_amounts(): void
    {
        $this->assertSame('-10.99 KES', Money::from(-10.99)->format());
    }

    public function test_format_pads_cents(): void
    {
        $this->assertSame('10.50 KES', Money::from(10.5)->format());
    }

    public function test_to_array_returns_amount_and_currency(): void
    {
        $this->assertSame(['amount' => 1099, 'currency' => 'KES'], Money::from(10.99)->toArray());
    }
}
