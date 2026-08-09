<?php

use App\Models\MenuItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;

test('customer scanning QR entry session route opens table session and binds session state', function () {
    $table = RestaurantTable::factory()->create(['is_active' => true]);

    $response = $this->get(route('session.entry', $table));

    $response->assertStatus(200);
    $this->assertDatabaseHas('table_sessions', [
        'table_id' => $table->id,
        'status' => 'open',
    ]);
});

test('customer can place a self-order with modifiers and special instructions', function () {
    $table = RestaurantTable::factory()->create(['is_active' => true]);
    $session = TableSession::factory()->create(['table_id' => $table->id, 'status' => 'open']);
    $menuItem = MenuItem::factory()->create(['base_price' => 15.00]);

    $response = $this->withSession(['active_session_id' => $session->id])->postJson(route('cart.complete'), [
        'session_id' => $session->id,
        'items' => [
            [
                'menu_item_id' => $menuItem->id,
                'quantity' => 2,
                'selected_modifiers' => [
                    ['name' => 'Extra Cheese', 'price' => 1.50],
                ],
                'special_instructions' => 'No onions please',
            ],
        ],
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('ok', true);

    $this->assertDatabaseHas('orders', [
        'session_id' => $session->id,
        'placed_by_role' => 'customer',
    ]);
    $this->assertDatabaseHas('order_items', [
        'menu_item_id' => $menuItem->id,
        'quantity' => 2,
        'special_instructions' => 'No onions please',
    ]);
});
