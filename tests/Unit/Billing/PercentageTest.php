<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\ValueObjects\Percentage;
use PHPUnit\Framework\TestCase;

final class PercentageTest extends TestCase
{
    public function test_it_can_be_created_from_percentage(): void
    {
        $this->assertSame(15, Percentage::fromPercentage(15)->toPercentage());
    }

    public function test_it_can_be_created_from_decimal(): void
    {
        $this->assertSame(15, Percentage::fromDecimal(0.15)->toPercentage());
    }

    public function test_it_converts_to_decimal(): void
    {
        $this->assertSame(0.15, Percentage::fromPercentage(15)->toDecimal());
    }

    public function test_it_applies_percentage_to_amount(): void
    {
        $this->assertSame(15, Percentage::fromPercentage(15)->apply(100));
    }

    public function test_it_clamps_below_minimum(): void
    {
        $this->assertSame(0, Percentage::fromPercentage(-10)->toPercentage());
    }

    public function test_it_clamps_above_maximum(): void
    {
        $this->assertSame(100, Percentage::fromPercentage(150)->toPercentage());
    }

    public function test_it_equals_compares_values_only(): void
    {
        $this->assertTrue(Percentage::fromPercentage(10)->equals(Percentage::fromPercentage(10)));
        $this->assertFalse(Percentage::fromPercentage(10)->equals(Percentage::fromPercentage(20)));
    }
}
