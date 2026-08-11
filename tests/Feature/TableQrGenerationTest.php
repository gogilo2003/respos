<?php

use App\Models\RestaurantTable;
use App\Models\User;

test('admin can view table SVG QR Code image', function () {
    $admin = User::factory()->withRole('admin')->create();
    $table = RestaurantTable::factory()->create();

    $response = $this->actingAs($admin)->get(route('tables.qr-image', $table));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'image/svg+xml');
    expect($response->getContent())->toContain('<svg');
});

test('admin can regenerate table QR code token', function () {
    $admin = User::factory()->withRole('admin')->create();
    $table = RestaurantTable::factory()->create();

    $response = $this->actingAs($admin)->post(route('tables.regenerate-qr', $table));

    $response->assertStatus(302);
    $this->assertDatabaseHas('qr_codes', [
        'table_id' => $table->id,
    ]);
});
