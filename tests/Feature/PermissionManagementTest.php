<?php

use App\Models\Role;
use App\Models\User;
use App\Services\PermissionRegistry;

test('permission registry catalog resolves default permissions per role', function () {
    $registry = new PermissionRegistry;

    $adminPerms = $registry->getDefaultPermissionsForRole('admin');
    $cashierPerms = $registry->getDefaultPermissionsForRole('cashier');

    expect($adminPerms)->toContain('users.index', 'roles.index', 'bills.index', 'reconciliations.index');
    expect($cashierPerms)->toContain('bills.index', 'reconciliations.index');
    expect($cashierPerms)->not->toContain('users.index', 'roles.index');
});

test('user hasPermission evaluates role JSON permissions with admin super override', function () {
    $cashierRole = Role::firstOrCreate(['name' => 'cashier'], [
        'permissions' => ['bills.index', 'reconciliations.index'],
    ]);
    $cashierRole->permissions = ['bills.index', 'reconciliations.index'];
    $cashierRole->save();

    $cashier = User::factory()->make(['role_id' => $cashierRole->id]);
    $cashier->setRelation('role', $cashierRole);

    expect($cashier->hasPermission('bills.index'))->toBeTrue();
    expect($cashier->hasPermission('users.index'))->toBeFalse();

    $adminRole = Role::firstOrCreate(['name' => 'admin']);
    $admin = User::factory()->make(['role_id' => $adminRole->id]);
    $admin->setRelation('role', $adminRole);

    expect($admin->hasPermission('any.custom.permission'))->toBeTrue();
});

test('permission middleware blocks user when required permission is missing', function () {
    $cashier = User::factory()->withRole('cashier')->create();

    $this->actingAs($cashier)
        ->get(route('roles.index'))
        ->assertStatus(403);
});

test('admin can update permissions for a role via role controller', function () {
    $admin = User::factory()->withRole('admin')->create();
    $role = Role::firstOrCreate(['name' => 'waiter']);

    $response = $this->actingAs($admin)->patch(route('roles.update', $role), [
        'permissions' => ['waiter.dashboard', 'kitchen.dashboard'],
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('roles', [
        'id' => $role->id,
    ]);

    $updatedRole = Role::find($role->id);
    expect($updatedRole->permissions)->toContain('waiter.dashboard', 'kitchen.dashboard');
});
