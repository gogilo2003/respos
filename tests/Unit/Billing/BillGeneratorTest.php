<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\Enums\BillStatus;
use App\Domain\Billing\Services\BillFactory;
use App\Domain\Billing\Services\BillGenerator;
use App\Domain\Billing\Services\BillNumberGenerator;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use PHPUnit\Framework\TestCase;

final class BillGeneratorTest extends TestCase
{
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = new Order();
        $this->order->id = 1;
        $this->order->session_id = 1;

        $table = new RestaurantTable();
        $table->table_number = 'A1';
        $table->customer = 'John Doe';

        $session = new TableSession();
        $session->id = 1;
        $session->setRelation('table', $table);

        $this->order->setRelation('session', $session);

        $menuItem = new MenuItem();
        $menuItem->name = 'Pasta';
        $menuItem->base_price = 10.00;

        $orderItem = new OrderItem();
        $orderItem->quantity = 2;
        $orderItem->unit_price = 10.00;
        $orderItem->setRelation('menuItem', $menuItem);

        $this->order->setRelation('items', collect([$orderItem]));
    }

    public function test_it_generates_bill_from_order(): void
    {
        $repo = $this->createMock(\App\Repositories\Contracts\BillRepositoryInterface::class);
        $repo->method('nextBillNumber')->willReturn('BILL-2026-000001');
        $numbers = new BillNumberGenerator($repo);

        $generator = new BillGenerator($numbers);
        $bill = $generator->generateForOrder($this->order);

        $this->assertInstanceOf(BillData::class, $bill);
        $this->assertSame('BILL-2026-000001', $bill->billNumber);
        $this->assertSame('John Doe', $bill->customer);
        $this->assertSame('A1', $bill->table);
        $this->assertSame('Order #1', $bill->order);
        $this->assertSame(BillStatus::Draft, $bill->status);
        $this->assertSame(1, $bill->sessionId);
    }

    public function test_it_preserves_order_items(): void
    {
        $repo = $this->createMock(\App\Repositories\Contracts\BillRepositoryInterface::class);
        $repo->method('nextBillNumber')->willReturn('BILL-2026-000001');
        $numbers = new BillNumberGenerator($repo);

        $generator = new BillGenerator($numbers);
        $bill = $generator->generateForOrder($this->order);

        $this->assertCount(1, $bill->items());
        $this->assertSame('Pasta', $bill->items()[0]['name']);
    }

    public function test_it_uses_draft_status(): void
    {
        $repo = $this->createMock(\App\Repositories\Contracts\BillRepositoryInterface::class);
        $repo->method('nextBillNumber')->willReturn('BILL-2026-000001');
        $numbers = new BillNumberGenerator($repo);

        $generator = new BillGenerator($numbers);
        $bill = $generator->generateForOrder($this->order);

        $this->assertSame(BillStatus::Draft, $bill->status);
    }
}
