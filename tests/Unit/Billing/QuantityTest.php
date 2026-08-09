<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\ValueObjects\Quantity;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function test_it_can_be_created_from_valid_integer(): void
    {
        $this->assertSame(1, Quantity::from(1)->value());
    }

    public function test_it_throws_for_zero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Quantity::from(0);
    }

    public function test_it_throws_for_negative_value(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Quantity::from(-1);
    }

    public function test_add_returns_new_instance(): void
    {
        $a = Quantity::from(2);
        $b = Quantity::from(3);

        $this->assertSame(5, $a->add($b)->value());
        $this->assertSame(2, $a->value());
        $this->assertSame(3, $b->value());
    }

    public function test_subtract_returns_new_instance(): void
    {
        $a = Quantity::from(5);
        $b = Quantity::from(2);

        $this->assertSame(3, $a->subtract($b)->value());
    }

    public function test_subtract_throws_when_result_is_less_than_one(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Quantity::from(1)->subtract(Quantity::from(2));
    }

    public function test_multiply_returns_new_instance(): void
    {
        $this->assertSame(6, Quantity::from(2)->multiply(3)->value());
    }

    public function test_multiply_throws_for_negative_multiplier(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Quantity::from(2)->multiply(-1);
    }

    public function test_equals_compares_values_only(): void
    {
        $this->assertTrue(Quantity::from(2)->equals(Quantity::from(2)));
        $this->assertFalse(Quantity::from(2)->equals(Quantity::from(3)));
    }
}
