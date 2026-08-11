<?php

use App\Models\User;

test('waiter can access waiter dashboard but is forbidden from user management', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $this->actingAs($waiter)
        ->get(route('waiter.dashboard'))
        ->assertStatus(200);

    $this->actingAs($waiter)
        ->get(route('users'))
        ->assertStatus(403);
});

test('cashier can access billing and reconciliation but is forbidden from audit logs', function () {
    $cashier = User::factory()->withRole('cashier')->create();

    $this->actingAs($cashier)
        ->get(route('bills.index'))
        ->assertStatus(200);

    $this->actingAs($cashier)
        ->get(route('audit-logs.index'))
        ->assertStatus(403);
});

test('kitchen staff can access kitchen dashboard but is forbidden from billing', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();

    $this->actingAs($kitchen)
        ->get(route('kitchen.dashboard'))
        ->assertStatus(200);

    $this->actingAs($kitchen)
        ->get(route('bills.index'))
        ->assertStatus(403);
});

test('admin can access user management and audit logs', function () {
    $admin = User::factory()->withRole('admin')->create();

    $this->actingAs($admin)
        ->get(route('users'))
        ->assertStatus(200);

    $this->actingAs($admin)
        ->get(route('audit-logs.index'))
        ->assertStatus(200);
});
