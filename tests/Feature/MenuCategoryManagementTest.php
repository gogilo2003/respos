<?php

use App\Models\MenuCategory;
use App\Models\User;

test('admin or manager can create a menu category', function () {
    $admin = User::factory()->withRole('admin')->create();

    $response = $this->actingAs($admin)->postJson(route('menu-categories.store'), [
        'name' => 'Desserts',
        'description' => 'Sweet treats and ice creams',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('menu_categories', [
        'name' => 'Desserts',
        'is_active' => true,
    ]);
});

test('manager can update a menu category', function () {
    $manager = User::factory()->withRole('manager')->create();
    $category = MenuCategory::factory()->create(['name' => 'Old Name']);

    $response = $this->actingAs($manager)->patchJson(route('menu-categories.update', $category), [
        'name' => 'Updated Name',
        'description' => 'Updated Description',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('menu_categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'is_active' => false,
    ]);
});

test('admin or manager can toggle category active status', function () {
    $manager = User::factory()->withRole('manager')->create();
    $category = MenuCategory::factory()->create(['is_active' => true]);

    $response = $this->actingAs($manager)->patchJson(route('menu-categories.toggle-active', $category));

    $response->assertStatus(302);
    $this->assertDatabaseHas('menu_categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});

test('waiter or cashier cannot create or delete a menu category', function () {
    $waiter = User::factory()->withRole('waiter')->create();
    $category = MenuCategory::factory()->create();

    $response = $this->actingAs($waiter)->postJson(route('menu-categories.store'), [
        'name' => 'Unauthorized Category',
        'is_active' => true,
    ]);
    $response->assertStatus(403);

    $deleteResponse = $this->actingAs($waiter)->deleteJson(route('menu-categories.destroy', $category));
    $deleteResponse->assertStatus(403);
});
