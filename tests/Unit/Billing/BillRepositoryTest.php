<?php

namespace Tests\Unit\Billing;

use App\Domain\Billing\DTOs\BillData;
use App\Domain\Billing\DTOs\BillFilter;
use App\Domain\Billing\Enums\BillStatus;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Repositories\BillRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;
use Tests\TestCase as LaravelTestCase;

final class BillRepositoryTest extends LaravelTestCase
{
    use RefreshDatabase;

    private BillRepository $repository;
    private \App\Models\User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billCounter = 0;

        $this->user = $this->createUser();
        $this->repository = new BillRepository();
    }

    public function test_it_can_create_a_bill(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);

        $bill = $this->makeBillData(sessionId: $session->id);
        $result = $this->repository->create($bill);

        $this->assertInstanceOf(BillData::class, $result);
        $this->assertNotNull($result->billNumber);
        $this->assertSame($session->id, $result->sessionId);
    }

    public function test_it_can_find_a_bill_by_id(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $bill = $this->createBill($session->id);

        $result = $this->repository->find($bill->id);

        $this->assertInstanceOf(BillData::class, $result);
        $this->assertSame($bill->bill_number, $result->billNumber);
    }

    public function test_it_can_find_a_bill_by_number(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $bill = $this->createBill($session->id);

        $result = $this->repository->findByNumber($bill->bill_number);

        $this->assertInstanceOf(BillData::class, $result);
        $this->assertSame($bill->id, $result->sessionId);
    }

    public function test_it_can_find_a_bill_by_order(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $order = $this->createOrder($session->id);
        $menuItem = $this->createMenuItem();
        $orderItem = $this->createOrderItem($order->id, $menuItem->id);
        $bill = $this->createBill($session->id);
        $this->createBillItem($bill->id, $orderItem->id);

        $result = $this->repository->findByOrder($order->id);

        $this->assertInstanceOf(BillData::class, $result);
        $this->assertSame($bill->id, $result->sessionId);
    }

    public function test_it_can_find_a_bill_by_status(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $bill = $this->createBill($session->id, ['status' => BillStatus::Open]);

        $result = $this->repository->findByStatus(BillStatus::Open);

        $this->assertInstanceOf(BillData::class, $result);
        $this->assertSame($bill->bill_number, $result->billNumber);
    }

    public function test_it_can_find_all_bills_with_status_filter(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $this->createBill($session->id, ['status' => BillStatus::Open]);
        $this->createBill($session->id, ['status' => BillStatus::Paid]);

        $results = $this->repository->findAll(BillFilter::from(status: BillStatus::Open));

        $this->assertCount(1, $results);
        $this->assertSame(BillStatus::Open, $results->first()->status);
    }

    public function test_it_can_find_all_bills_with_table_filter(): void
    {
        $table1 = $this->createTable(['table_number' => 'T1']);
        $table2 = $this->createTable(['table_number' => 'T2']);
        $session1 = $this->createSession($table1->id);
        $session2 = $this->createSession($table2->id);
        $this->createBill($session1->id);
        $this->createBill($session2->id);

        $results = $this->repository->findAll(BillFilter::from(table: $table1->id));

        $this->assertCount(1, $results);
    }

    public function test_it_can_find_all_bills_with_cashier_filter(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $user2 = $this->createUser(['username' => 'cashier2', 'email' => 'cashier2@example.com']);
        $this->createBill($session->id, ['generatedBy' => $this->user->id]);
        $this->createBill($session->id, ['generatedBy' => $user2->id]);

        $results = $this->repository->findAll(BillFilter::from(cashierId: $this->user->id));

        $this->assertCount(1, $results);
        $this->assertSame($this->user->id, $results->first()->generatedBy);
    }

    public function test_it_can_find_all_bills_with_date_range_filter(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $bill = $this->createBill($session->id);

        $from = $bill->generated_at ? $bill->generated_at->toDateString() : now()->toDateString();
        $to = $bill->generated_at ? $bill->generated_at->toDateString() : now()->toDateString();

        $results = $this->repository->findAll(BillFilter::from(from: $from, to: $to));

        $this->assertCount(1, $results);
    }

    public function test_it_can_find_open_bills(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $this->createBill($session->id, ['status' => BillStatus::Open]);
        $this->createBill($session->id, ['status' => BillStatus::Paid]);

        $results = $this->repository->findOpenBills();

        $this->assertCount(1, $results);
        $this->assertSame(BillStatus::Open, $results->first()->status);
    }

    public function test_it_can_find_paid_bills(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $this->createBill($session->id, ['status' => BillStatus::Open]);
        $this->createBill($session->id, ['status' => BillStatus::Paid]);

        $results = $this->repository->findPaidBills();

        $this->assertCount(1, $results);
        $this->assertSame(BillStatus::Paid, $results->first()->status);
    }

    public function test_it_can_generate_next_bill_number(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $this->createBill($session->id, ['billNumber' => 'BILL-2026-000001']);

        $next = $this->repository->nextBillNumber('BILL', 2026);

        $this->assertSame('BILL-2026-000002', $next);
    }

    public function test_it_can_update_a_bill(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $bill = $this->createBill($session->id);

        $updated = $this->repository->update(BillData::from(
            billNumber: $bill->bill_number,
            customer: 'Updated Customer',
            table: $session->table->table_number,
            order: null,
            items: [],
            subtotal: 0,
            discount: 0,
            tax: 0,
            serviceCharge: 0,
            grandTotal: 0,
            status: BillStatus::Open,
            createdAt: new \DateTimeImmutable(),
            sessionId: $session->id,
            generatedBy: $bill->generated_by,
            discountApprovedBy: null,
            discountReason: null,
            voidedBy: null,
            voidReason: null,
            paidAt: null,
            voidedAt: null,
        ));

        $this->assertInstanceOf(BillData::class, $updated);
        $this->assertSame(BillStatus::Open, $updated->status);
    }

    public function test_it_can_delete_a_bill(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $bill = $this->createBill($session->id);

        $result = $this->repository->delete($bill->id);

        $this->assertTrue($result);
        $this->assertNull($this->repository->find($bill->id));
    }

    public function test_it_can_check_if_bill_exists_for_order(): void
    {
        $table = $this->createTable();
        $session = $this->createSession($table->id);
        $order = $this->createOrder($session->id);
        $menuItem = $this->createMenuItem();
        $orderItem = $this->createOrderItem($order->id, $menuItem->id);
        $bill = $this->createBill($session->id);
        $this->createBillItem($bill->id, $orderItem->id);

        $this->assertTrue($this->repository->existsForOrder($order->id));
        $this->assertFalse($this->repository->existsForOrder(999));
    }

    private function createTable(array $attributes = []): RestaurantTable
    {
        return RestaurantTable::create(array_merge([
            'table_number' => 'T' . RestaurantTable::count() + 1,
            'capacity' => 4,
            'location' => 'Main Floor',
            'status' => 'available',
            'is_active' => true,
        ], $attributes));
    }

    private function createUser(array $attributes = []): \App\Models\User
    {
        return \App\Models\User::create(array_merge([
            'role_id' => \App\Models\Role::where('name', 'cashier')->first()?->id ?? 1,
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ], $attributes));
    }

    private function createSession(int $tableId, array $attributes = []): TableSession
    {
        return TableSession::create(array_merge([
            'table_id' => $tableId,
            'session_token' => 'token-' . uniqid(),
            'status' => 'open',
            'token_expires_at' => now()->addHours(2),
        ], $attributes));
    }

    private function createOrder(int $sessionId, array $attributes = []): Order
    {
        return Order::create(array_merge([
            'session_id' => $sessionId,
            'placed_by_role' => 'waiter',
            'placed_by_user' => 1,
            'status' => 'pending',
        ], $attributes));
    }

    private function createMenuItem(array $attributes = []): MenuItem
    {
        $category = \App\Models\MenuCategory::create([
            'name' => 'Test Category',
            'description' => 'Test',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return MenuItem::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Item',
            'base_price' => 10.00,
            'tax_inclusive' => true,
            'prep_time_min' => 10,
            'is_available' => true,
        ], $attributes));
    }

    private function createOrderItem(int $orderId, int $menuItemId, array $attributes = []): OrderItem
    {
        return OrderItem::create(array_merge([
            'order_id' => $orderId,
            'menu_item_id' => $menuItemId,
            'quantity' => 1,
            'unit_price' => 10.00,
        ], $attributes));
    }

    private int $billCounter = 0;

    private function createBill(int $sessionId, array $attributes = []): Bill
    {
        $this->billCounter++;
        $attributes['billNumber'] = $attributes['billNumber'] ?? 'BILL-2026-' . str_pad((string) $this->billCounter, 6, '0', STR_PAD_LEFT);
        $attributes['sessionId'] = $sessionId;
        $billData = $this->makeBillData(...$attributes);
        $result = $this->repository->create($billData);

        return Bill::where('bill_number', $result->billNumber)->firstOrFail();
    }

    private function createBillItem(int $billId, int $orderItemId, array $attributes = []): BillItem
    {
        return BillItem::create(array_merge([
            'bill_id' => $billId,
            'order_item_id' => $orderItemId,
            'quantity' => 1,
            'unit_price' => 10.00,
            'line_total' => 10.00,
        ], $attributes));
    }

    private function makeBillData(string $billNumber = 'BILL-2026-000001', BillStatus $status = BillStatus::Draft, ?int $sessionId = null, ?int $generatedBy = null): BillData
    {
        return BillData::from(
            billNumber: $billNumber,
            customer: 'Test Customer',
            table: 'A1',
            order: null,
            items: [],
            subtotal: 0,
            discount: 0,
            tax: 0,
            serviceCharge: 0,
            grandTotal: 0,
            status: $status,
            createdAt: new \DateTimeImmutable(),
            sessionId: $sessionId,
            generatedBy: $generatedBy ?? $this->user->id,
            discountApprovedBy: null,
            discountReason: null,
            voidedBy: null,
            voidReason: null,
            paidAt: null,
            voidedAt: null,
        );
    }
}
