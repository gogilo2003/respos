<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('inertia response shares navigationMenu prop filtered by role', function () {
    $admin = User::factory()->withRole('admin')->create();

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard/Admin')
        ->has('navigationMenu')
    );
});

test('inertia response shares empty navigationMenu prop for guests', function () {
    $response = $this->get(route('categories'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->has('navigationMenu', 0)
    );
});
