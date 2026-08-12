<?php

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Models\User;

test('waiter can transition pending order status to accepted', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'pending']);
    OrderItem::factory()->create(['order_id' => $order->id, 'status' => 'pending']);

    $response = $this->actingAs($waiter)->patchJson(route('orders.status.update', $order), [
        'status' => 'accepted',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'accepted',
    ]);
});

test('kitchen staff cannot accept pending order but can transition accepted order to preparing or ready', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();
    $session = TableSession::factory()->create();
    $pendingOrder = Order::factory()->create(['session_id' => $session->id, 'status' => 'pending']);

    // Kitchen cannot accept pending order
    $response = $this->actingAs($kitchen)->patchJson(route('orders.status.update', $pendingOrder), [
        'status' => 'accepted',
    ]);
    $response->assertStatus(403);

    // Kitchen can transition accepted order to preparing
    $acceptedOrder = Order::factory()->create(['session_id' => $session->id, 'status' => 'accepted']);
    OrderItem::factory()->create(['order_id' => $acceptedOrder->id, 'status' => 'accepted']);

    $prepResponse = $this->actingAs($kitchen)->patchJson(route('orders.status.update', $acceptedOrder), [
        'status' => 'preparing',
    ]);
    $prepResponse->assertStatus(200);
    $this->assertDatabaseHas('orders', [
        'id' => $acceptedOrder->id,
        'status' => 'preparing',
    ]);
});

test('waiter can mark order as served', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'ready']);
    OrderItem::factory()->create(['order_id' => $order->id, 'status' => 'ready']);

    $response = $this->actingAs($waiter)->patchJson(route('orders.status.update', $order), [
        'status' => 'served',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'served',
    ]);
});

test('waiter can mark served order as billed', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'served']);

    $response = $this->actingAs($waiter)->patchJson(route('orders.status.update', $order), [
        'status' => 'billed',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'billed',
    ]);
});

test('manager can mark served order as billed', function () {
    $manager = User::factory()->withRole('manager')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'served']);

    $response = $this->actingAs($manager)->patchJson(route('orders.status.update', $order), [
        'status' => 'billed',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'billed',
    ]);
});

test('kitchen staff cannot mark served order as billed', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'served']);

    $response = $this->actingAs($kitchen)->patchJson(route('orders.status.update', $order), [
        'status' => 'billed',
    ]);

    $response->assertStatus(403);
});

test('waiter can add item to served order and order reopens to accepted', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'served']);
    OrderItem::factory()->create(['order_id' => $order->id, 'status' => 'served']);

    $response = $this->actingAs($waiter)->post(route('orders.items.add', ['order' => $order->id]), [
        'menu_item_id' => MenuItem::factory()->create()->id,
        'quantity' => 1,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'accepted',
    ]);
});

test('waiter cannot add item to billed order', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $session = TableSession::factory()->create();
    $order = Order::factory()->create(['session_id' => $session->id, 'status' => 'billed']);
    OrderItem::factory()->create(['order_id' => $order->id, 'status' => 'served']);

    $response = $this->actingAs($waiter)->post(route('orders.items.add', ['order' => $order->id]), [
        'menu_item_id' => MenuItem::factory()->create()->id,
        'quantity' => 2,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'billed',
    ]);
});
