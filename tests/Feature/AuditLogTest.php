<?php

use App\Models\Bill;
use App\Models\User;

test('admin can view audit logs page', function () {
    $admin = User::factory()->withRole('admin')->create();

    $response = $this->actingAs($admin)->get(route('audit-logs.index'));

    $response->assertStatus(200);
});

test('voiding a bill records an audit log entry', function () {
    $admin = User::factory()->withRole('admin')->create();
    $bill = Bill::factory()->create(['status' => 'open']);

    $response = $this->actingAs($admin)->patch(route('bills.void', $bill));

    $response->assertStatus(302);
    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $admin->id,
        'action' => 'bill_voided',
        'entity_id' => $bill->id,
    ]);
});
