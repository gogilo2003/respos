<?php

use App\Models\Role;
use App\Models\User;
use App\Services\NavigationMenuService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = new NavigationMenuService();
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
    expect($keys)->toBe(['dashboard', 'users', 'menu-categories', 'menu-items', 'tables', 'bills']);
});

test('manager user receives manager navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'manager']);
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard', 'menu-categories', 'menu-items', 'tables', 'bills']);
});

test('cashier user receives cashier navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'cashier']);
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard', 'bills']);
});

test('waiter user receives dashboard navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'waiter']);
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard']);
});

test('kitchen user receives dashboard navigation menu', function () {
    $role = Role::firstOrCreate(['name' => 'kitchen']);
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    $menu = $this->service->getNavigationMenu($user);

    $keys = array_column($menu, 'key');
    expect($keys)->toBe(['dashboard']);
});
