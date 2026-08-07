<?php

use App\Models\User;
use App\Services\DashboardService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->dashboardService = app(DashboardService::class);
});

test('admin role returns Admin component and admin stats', function () {
    $admin = User::factory()->withRole('admin')->create();

    $payload = $this->dashboardService->getDashboardPayload($admin);

    expect($payload)->toHaveKey('component', 'Dashboard/Admin');
    expect($payload)->toHaveKey('props');
    expect($payload['props'])->toHaveKey('statistics');
    expect($payload['props'])->toHaveKey('recent_orders');
});

test('cashier role returns Cashier component and cashier stats', function () {
    $cashier = User::factory()->withRole('cashier')->create();

    $payload = $this->dashboardService->getDashboardPayload($cashier);

    expect($payload)->toHaveKey('component', 'Dashboard/Cashier');
    expect($payload)->toHaveKey('props');
    expect($payload['props'])->toHaveKey('statistics');
    expect($payload['props'])->toHaveKey('recent_open_bills');
});

test('waiter role returns Waiter/Dashboard component and waiter statistics', function () {
    $waiter = User::factory()->withRole('waiter')->create();

    $payload = $this->dashboardService->getDashboardPayload($waiter);

    expect($payload)->toHaveKey('component', 'Waiter/Dashboard');
    expect($payload)->toHaveKey('props');
    expect($payload['props'])->toHaveKey('statistics');
});

test('kitchen role returns Kitchen/Dashboard component and kitchen dashboard data', function () {
    $kitchen = User::factory()->withRole('kitchen')->create();

    $payload = $this->dashboardService->getDashboardPayload($kitchen);

    expect($payload)->toHaveKey('component', 'Kitchen/Dashboard');
    expect($payload)->toHaveKey('props');
    expect($payload['props'])->toHaveKey('pending_orders');
});

test('unauthenticated user returns redirect payload', function () {
    $payload = $this->dashboardService->getDashboardPayload(null);

    expect($payload)->toHaveKey('redirect');
});
