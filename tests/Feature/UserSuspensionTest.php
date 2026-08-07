<?php

use App\Models\User;

test('admin can toggle user suspension status', function () {
    $admin = User::factory()->withRole('admin')->create();
    $staff = User::factory()->withRole('waiter')->create(['is_active' => true]);

    $response = $this->actingAs($admin)->patch(route('users.toggle-status', $staff));

    $response->assertStatus(302);
    $this->assertDatabaseHas('users', [
        'id' => $staff->id,
        'is_active' => false,
    ]);
});

test('deactivated user is logged out and blocked by middleware', function () {
    $suspendedStaff = User::factory()->withRole('waiter')->create(['is_active' => false]);

    $response = $this->actingAs($suspendedStaff)->get(route('dashboard'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
});
