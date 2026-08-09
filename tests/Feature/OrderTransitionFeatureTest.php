<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Models\User;

test('waiter or kitchen staff can transition order status to accepted or served', function () {
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
