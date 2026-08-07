<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->activeCategory = MenuCategory::factory()->create([
        'name' => 'Starters',
        'is_active' => true,
    ]);

    $this->inactiveCategory = MenuCategory::factory()->create([
        'name' => 'Seasonal Specials',
        'is_active' => false,
    ]);

    $this->availableItem = MenuItem::factory()->create([
        'category_id' => $this->activeCategory->id,
        'name' => 'Spring Rolls',
        'is_available' => true,
    ]);

    $this->unavailableItem = MenuItem::factory()->create([
        'category_id' => $this->activeCategory->id,
        'name' => 'Lobster Roll',
        'is_available' => false,
    ]);
});

test('public guest views only active categories and available items on menu page', function () {
    $response = $this->get(route('menu'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Menu')
        ->has('menuItems', fn (Assert $json) => $json
            ->where('0.name', 'Spring Rolls')
            ->missing('1.name')
            ->etc()
        )
    );
});

test('public guest views active categories on categories page', function () {
    $response = $this->get(route('categories'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Categories')
        ->has('categories', 1)
        ->where('categories.0.name', 'Starters')
    );
});

test('admin views all categories and items including inactive and out of stock', function () {
    $admin = User::factory()->withRole('admin')->create();

    $response = $this->actingAs($admin)->get(route('menu'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Menu')
        ->has('menuItems', 2)
    );
});
