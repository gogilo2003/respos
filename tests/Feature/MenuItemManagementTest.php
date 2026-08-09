<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;

test('admin or manager can create a menu item with modifier groups', function () {
    $admin = User::factory()->withRole('admin')->create();
    $category = MenuCategory::factory()->create();

    $response = $this->actingAs($admin)->postJson(route('menu-items.store'), [
        'category_id' => $category->id,
        'name' => 'Deluxe Pizza',
        'description' => 'Loaded with toppings',
        'base_price' => 18.50,
        'tax_inclusive' => true,
        'prep_time_min' => 15,
        'is_available' => true,
        'sort_order' => 1,
        'modifier_groups' => [
            [
                'name' => 'Size',
                'required' => true,
                'options' => [
                    ['name' => 'Large', 'price' => 2.50],
                ],
            ],
        ],
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('menu_items', [
        'name' => 'Deluxe Pizza',
        'category_id' => $category->id,
        'is_available' => true,
    ]);
});

test('kitchen staff can toggle menu item availability', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();
    $item = MenuItem::factory()->create(['is_available' => true]);

    $response = $this->actingAs($kitchen)->patchJson(route('menu-items.toggle-availability', $item));

    $response->assertStatus(302);
    $this->assertDatabaseHas('menu_items', [
        'id' => $item->id,
        'is_available' => false,
    ]);
});

test('waiter cannot create or delete a menu item', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $category = MenuCategory::factory()->create();
    $item = MenuItem::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($waiter)->postJson(route('menu-items.store'), [
        'category_id' => $category->id,
        'name' => 'Unauthorized Item',
        'base_price' => 10.00,
        'tax_inclusive' => true,
        'prep_time_min' => 10,
        'is_available' => true,
    ]);
    $response->assertStatus(403);

    $deleteResponse = $this->actingAs($waiter)->deleteJson(route('menu-items.destroy', $item));
    $deleteResponse->assertStatus(403);
});
