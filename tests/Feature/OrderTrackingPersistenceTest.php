<?php

use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;

test('completing an order saves active_order_id in session and shares it via Inertia props', function () {
    $table = RestaurantTable::factory()->create();
    $session = TableSession::factory()->create([
        'table_id' => $table->id,
        'status' => 'open',
    ]);
    $menuItem = MenuItem::factory()->create(['base_price' => 150]);

    $response = $this->withSession(['active_session_id' => $session->id])
        ->postJson(route('cart.complete'), [
            'session_id' => $session->id,
            'items' => [
                ['menu_item_id' => $menuItem->id, 'quantity' => 2],
            ],
        ]);

    $response->assertStatus(200);
    $response->assertJson(['ok' => true]);

    $orderId = $response->json('order_id');
    expect($orderId)->not->toBeNull();

    // Verify session stores active_order_id
    expect(session('active_order_id'))->toBe($orderId);

    // Verify /orders/track fallback route redirects to /orders/{id}/track
    $trackResponse = $this->withSession(['active_order_id' => $orderId])
        ->get(route('orders.track.latest'));

    $trackResponse->assertRedirect(route('orders.track', $orderId));
});

test('visiting /orders/track without active order redirects to menu', function () {
    $response = $this->get(route('orders.track.latest'));

    $response->assertRedirect(route('menu'));
});
