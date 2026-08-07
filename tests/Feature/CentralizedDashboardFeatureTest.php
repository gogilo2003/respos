<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin visiting /dashboard receives Dashboard/Admin component and stats', function () {
    $admin = User::factory()->withRole('admin')->create();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard/Admin')
        ->has('statistics')
        ->has('recent_orders')
    );
});

test('cashier visiting /dashboard receives Dashboard/Cashier component and billing stats', function () {
    $cashier = User::factory()->withRole('cashier')->create();

    $response = $this->actingAs($cashier)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard/Cashier')
        ->has('statistics')
        ->has('recent_open_bills')
    );
});

test('waiter visiting /dashboard receives Waiter/Dashboard component and stats', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $response = $this->actingAs($waiter)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Waiter/Dashboard')
        ->has('statistics')
    );
});

test('kitchen visiting /dashboard receives Kitchen/Dashboard component and data', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();

    $response = $this->actingAs($kitchen)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Kitchen/Dashboard')
        ->has('statistics')
        ->has('pending_orders')
    );
});
