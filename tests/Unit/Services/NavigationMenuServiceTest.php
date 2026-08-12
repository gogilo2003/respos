<?php

use App\Models\Role;
use App\Models\User;
use App\Services\NavigationMenuService;
use App\Services\PermissionRegistry;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = new NavigationMenuService;
});

test('null user receives empty navigation menu', function () {
    $menu = $this->service->getNavigationMenu(null);

    expect($menu)->toBeArray()->toBeEmpty();
});

test('admin user receives full admin navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'admin']);
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe([
        'dashboard',
        'orders.index',
        'users.index',
        'roles.index',
        'menu-categories.index',
        'menu-items.index',
        'tables.index',
        'bills.index',
        'kitchen.dashboard',
        'waiter.dashboard',
        'reconciliations.index',
        'audit-logs.index',
    ]);
});

test('manager user receives manager navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'manager']);
    $role->permissions = app(PermissionRegistry::class)->getDefaultPermissionsForRole('manager');

    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe([
        'dashboard',
        'orders.index',
        'menu-categories.index',
        'menu-items.index',
        'tables.index',
        'bills.index',
        'kitchen.dashboard',
        'waiter.dashboard',
        'reconciliations.index',
    ]);
});

test('cashier user receives cashier navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'cashier']);
    $role->permissions = app(PermissionRegistry::class)->getDefaultPermissionsForRole('cashier');

    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard', 'orders.index', 'bills.index', 'reconciliations.index']);
});

test('waiter user receives waiter navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'waiter']);
    $role->permissions = app(PermissionRegistry::class)->getDefaultPermissionsForRole('waiter');

    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard', 'orders.index', 'waiter.dashboard']);
});

test('kitchen user receives kitchen navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'kitchen']);
    $role->permissions = app(PermissionRegistry::class)->getDefaultPermissionsForRole('kitchen');

    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard', 'orders.index', 'kitchen.dashboard']);
});
