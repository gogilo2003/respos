<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\MenuService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->menuService = app(MenuService::class);

    $this->activeCat = MenuCategory::factory()->create(['name' => 'Active Cat', 'is_active' => true]);
    $this->inactiveCat = MenuCategory::factory()->create(['name' => 'Inactive Cat', 'is_active' => false]);

    $this->availableItem = MenuItem::factory()->create([
        'category_id' => $this->activeCat->id,
        'name' => 'Burger Available',
        'is_available' => true,
    ]);

    $this->unavailableItem = MenuItem::factory()->create([
        'category_id' => $this->activeCat->id,
        'name' => 'Burger Out Of Stock',
        'is_available' => false,
    ]);

    $this->hiddenCatItem = MenuItem::factory()->create([
        'category_id' => $this->inactiveCat->id,
        'name' => 'Hidden Cat Item',
        'is_available' => true,
    ]);
});

test('customer/guest receives only active categories and available items', function () {
    $categories = $this->menuService->getMenuCategories(null);
    $items = $this->menuService->getMenuItems(null);

    $catNames = array_column($categories, 'name');
    expect($catNames)->toContain('Active Cat');
    expect($catNames)->not->toContain('Inactive Cat');

    $itemNames = array_column($items, 'name');
    expect($itemNames)->toContain('Burger Available');
    expect($itemNames)->not->toContain('Burger Out Of Stock', 'Hidden Cat Item');
});

test('waiter receives active categories and all items with availability flags', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $categories = $this->menuService->getMenuCategories($waiter);
    $items = $this->menuService->getMenuItems($waiter);

    $catNames = array_column($categories, 'name');
    expect($catNames)->toContain('Active Cat');
    expect($catNames)->not->toContain('Inactive Cat');

    $itemNames = array_column($items, 'name');
    expect($itemNames)->toContain('Burger Available', 'Burger Out Of Stock');
    expect($itemNames)->not->toContain('Hidden Cat Item');
});

test('admin receives all categories and all items', function () {
    $admin = User::factory()->withRole('admin')->create();

    $categories = $this->menuService->getMenuCategories($admin);
    $items = $this->menuService->getMenuItems($admin);

    $catNames = array_column($categories, 'name');
    expect($catNames)->toContain('Active Cat', 'Inactive Cat');

    $itemNames = array_column($items, 'name');
    expect($itemNames)->toContain('Burger Available', 'Burger Out Of Stock', 'Hidden Cat Item');
});

test('getCentralizedMenu structures categories with nested items by role', function () {
    $menu = $this->menuService->getCentralizedMenu(null);

    expect($menu)->toBeArray()->not->toBeEmpty();
    $firstCat = $menu[0];
    expect($firstCat)->toHaveKey('items');
    expect($firstCat['items'])->toBeArray();
});
