<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Services\BillFactory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use PHPUnit\Framework\TestCase;

final class BillFactoryTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = new Order;
        $this->order->id = 1;
        $this->order->session_id = 1;

        $table = new RestaurantTable;
        $table->table_number = 'A1';
        $table->customer = 'John Doe';

        $session = new TableSession;
        $session->id = 1;
        $session->setRelation('table', $table);

        $this->order->setRelation('session', $session);

        $menuItem = new MenuItem;
        $menuItem->name = 'Pasta';
        $menuItem->base_price = 10.00;

        $orderItem = new OrderItem;
        $orderItem->quantity = 2;
        $orderItem->unit_price = 10.00;
        $orderItem->setRelation('menuItem', $menuItem);

        $this->order->setRelation('items', collect([$orderItem]));
    }

    public function test_it_converts_order_to_bill_data(): void
    {
        $bill = BillFactory::fromOrder($this->order);

        $this->assertInstanceOf(BillData::class, $bill);
        $this->assertSame('BILL-ORDER-1-DRAFT', $bill->billNumber);
        $this->assertSame('John Doe', $bill->customer);
        $this->assertSame('A1', $bill->table);
        $this->assertSame('Order #1', $bill->order);
        $this->assertSame(BillStatus::Draft, $bill->status);
        $this->assertSame(1, $bill->sessionId);
    }

    public function test_it_maps_order_items(): void
    {
        $bill = BillFactory::fromOrder($this->order);

        $this->assertCount(1, $bill->items());
        $this->assertSame('Pasta', $bill->items()[0]['name']);
        $this->assertSame(2, $bill->items()[0]['quantity']);
        $this->assertSame(10.00, $bill->items()[0]['unit_price']);
        $this->assertSame(20.00, $bill->items()[0]['line_total']);
    }

    public function test_it_handles_missing_menu_item(): void
    {
        $orderItem = new OrderItem;
        $orderItem->quantity = 1;
        $orderItem->unit_price = null;
        $orderItem->setRelation('menuItem', null);

        $order = new Order;
        $order->id = 2;
        $order->session_id = 1;
        $order->setRelation('session', $this->order->session);
        $order->setRelation('items', collect([$orderItem]));

        $bill = BillFactory::fromOrder($order);

        $this->assertSame('Unknown Item', $bill->items()[0]['name']);
        $this->assertSame(0.00, $bill->items()[0]['unit_price']);
        $this->assertSame(0.00, $bill->items()[0]['line_total']);
    }

    public function test_it_sets_zero_monetary_fields(): void
    {
        $bill = BillFactory::fromOrder($this->order);

        $this->assertSame(0, $bill->subtotal->amountInCents());
        $this->assertSame(0, $bill->discount->amountInCents());
        $this->assertSame(0, $bill->tax->amountInCents());
        $this->assertSame(0, $bill->serviceCharge->amountInCents());
        $this->assertSame(0, $bill->grandTotal->amountInCents());
    }
}
