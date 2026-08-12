<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->table = RestaurantTable::factory()->create(['table_number' => 'T12']);
    $this->session = TableSession::factory()->create([
        'table_id' => $this->table->id,
        'status' => 'open',
    ]);
    $this->category = MenuCategory::factory()->create(['is_active' => true]);
    $this->menuItem = MenuItem::factory()->create([
        'category_id' => $this->category->id,
        'name' => 'Garlic Bread',
        'base_price' => 5.50,
        'is_available' => true,
    ]);
});

test('authorized staff can view orders listing page', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $response = $this->actingAs($waiter)->get(route('orders.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Orders/Index')
        ->has('orders')
        ->has('activeSessions')
        ->has('menuItems')
    );
});

test('waiter can create a new order for an active table session', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $response = $this->actingAs($waiter)->post(route('orders.store'), [
        'table_session_id' => $this->session->id,
        'items' => [
            [
                'menu_item_id' => $this->menuItem->id,
                'quantity' => 2,
                'special_instructions' => 'Extra crispy',
            ],
        ],
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', [
        'session_id' => $this->session->id,
        'status' => 'accepted',
    ]);
    $this->assertDatabaseHas('order_items', [
        'menu_item_id' => $this->menuItem->id,
        'quantity' => 2,
        'special_instructions' => 'Extra crispy',
        'status' => 'pending',
    ]);
});

test('kitchen staff can update order item status', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();
    $order = Order::factory()->create([
        'session_id' => $this->session->id,
        'status' => 'accepted',
    ]);
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $this->menuItem->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($kitchen)->patch(route('orders.items.update', [
        'order' => $order->id,
        'item' => $orderItem->id,
    ]), [
        'status' => 'preparing',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('order_items', [
        'id' => $orderItem->id,
        'status' => 'preparing',
    ]);
});

test('waiter can add an item to an existing active order', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $order = Order::factory()->create([
        'session_id' => $this->session->id,
        'status' => 'accepted',
    ]);

    $response = $this->actingAs($waiter)->post(route('orders.items.add', ['order' => $order->id]), [
        'menu_item_id' => $this->menuItem->id,
        'quantity' => 3,
        'special_instructions' => 'No butter',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'menu_item_id' => $this->menuItem->id,
        'quantity' => 3,
        'special_instructions' => 'No butter',
    ]);
});

test('waiter can remove an item from an active order', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $order = Order::factory()->create([
        'session_id' => $this->session->id,
        'status' => 'accepted',
    ]);
    $orderItem = OrderItem::factory()->create([
        'order_id' => $order->id,
        'menu_item_id' => $this->menuItem->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($waiter)->delete(route('orders.items.remove', [
        'order' => $order->id,
        'item' => $orderItem->id,
    ]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('order_items', [
        'id' => $orderItem->id,
    ]);
});
