<?php

use App\Models\Notification;
use App\Models\User;

test('authenticated staff user can poll for updates', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $response = $this->actingAs($waiter)->getJson(route('api.polling.updates'));

    $response->assertStatus(200)
        ->assertJsonPath('ok', true)
        ->assertJsonStructure(['ok', 'summary', 'notifications', 'unread_count', 'server_time']);
});

test('user can mark notification as read via polling api', function () {
    $staff = User::factory()->withRole('kitchen')->create();
    $notification = Notification::create([
        'target_role' => 'kitchen',
        'event_type' => 'order_placed',
        'is_read' => false,
    ]);

    $response = $this->actingAs($staff)->postJson(route('api.polling.mark-read', $notification->id));

    $response->assertStatus(200)
        ->assertJsonPath('ok', true);

    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'is_read' => true,
    ]);
});
