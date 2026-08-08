<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillItemData;
use PHPUnit\Framework\TestCase;

final class BillItemDataTest extends TestCase
{
    public function test_it_can_be_created_from_scalar_values(): void
    {
        $dto = BillItemData::from(
            menuItem: 'Pasta',
            quantity: 2,
            unitPrice: 10.50,
            discount: 1.00,
            tax: 2.10,
            lineTotal: 11.60,
            specialNotes: 'No onions',
        );

        $this->assertSame('Pasta', $dto->menuItem);
        $this->assertSame(2, $dto->quantity->value());
        $this->assertSame(1050, $dto->unitPrice->amountInCents());
        $this->assertSame(100, $dto->discount->amountInCents());
        $this->assertSame(210, $dto->tax->amountInCents());
        $this->assertSame(1160, $dto->lineTotal->amountInCents());
        $this->assertSame('No onions', $dto->specialNotes);
    }

    public function test_special_notes_are_optional(): void
    {
        $dto = BillItemData::from(
            menuItem: 'Salad',
            quantity: 1,
            unitPrice: 5.00,
            discount: 0.00,
            tax: 0.75,
            lineTotal: 5.75,
        );

        $this->assertNull($dto->specialNotes);
    }

    public function test_it_throws_for_invalid_quantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        BillItemData::from(
            menuItem: 'Soup',
            quantity: 0,
            unitPrice: 4.00,
            discount: 0.00,
            tax: 0.60,
            lineTotal: 4.60,
        );
    }
}
