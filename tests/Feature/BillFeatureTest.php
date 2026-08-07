<?php

use App\Models\Bill;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Models\User;

describe('Generate Bill', function () {
    test('authorized cashier can generate a bill from order', function () {
        $cashier = User::factory()->withRole('cashier')->create();
        $session = TableSession::factory()->create();
        $order = Order::factory()->create(['session_id' => $session->id]);
        OrderItem::factory()->create(['order_id' => $order->id]);

        $response = $this->actingAs($cashier)->postJson(route('bills.store'), [
            'session_id' => $session->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.session_id', $session->id)
            ->assertJsonPath('data.status', 'draft');

        $this->assertDatabaseHas('bills', [
            'session_id' => $session->id,
            'status' => 'draft',
        ]);
    });

    test('unauthorized waiter cannot generate a bill', function () {
        $waiter = User::factory()->withRole('waiter')->create();
        $session = TableSession::factory()->create();

        $response = $this->actingAs($waiter)->postJson(route('bills.store'), [
            'session_id' => $session->id,
        ]);

        $response->assertStatus(403);
    });

    test('unauthenticated user cannot generate a bill', function () {
        $session = TableSession::factory()->create();

        $response = $this->postJson(route('bills.store'), [
            'session_id' => $session->id,
        ]);

        $response->assertStatus(401);
    });
});

describe('Retrieve Bill', function () {
    test('authorized cashier can retrieve a bill', function () {
        $cashier = User::factory()->withRole('cashier')->create();
        $session = TableSession::factory()->create();
        $bill = Bill::factory()->create([
            'session_id' => $session->id,
            'generated_by' => $cashier->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($cashier)->getJson(route('bills.show', $bill));

        $response->assertStatus(200)
            ->assertJsonPath('data.bill_number', $bill->bill_number)
            ->assertJsonPath('data.session_id', $session->id);
    });

    test('unauthorized kitchen user cannot retrieve a bill', function () {
        $kitchen = User::factory()->withRole('kitchen')->create();
        $bill = Bill::factory()->create();

        $response = $this->actingAs($kitchen)->getJson(route('bills.show', $bill));

        $response->assertStatus(403);
    });
});

describe('List Bills', function () {
    test('authorized cashier can list all bills', function () {
        $cashier = User::factory()->withRole('cashier')->create();
        Bill::factory()->count(3)->create();

        $response = $this->actingAs($cashier)->getJson(route('bills.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    });

    test('unauthorized waiter cannot list bills', function () {
        $waiter = User::factory()->withRole('waiter')->create();
        Bill::factory()->count(2)->create();

        $response = $this->actingAs($waiter)->getJson(route('bills.index'));

        $response->assertStatus(403);
    });
});

describe('Void Bill', function () {
    test('manager or admin can void a bill', function () {
        $manager = User::factory()->withRole('manager')->create();
        $session = TableSession::factory()->create();
        $bill = Bill::factory()->create([
            'session_id' => $session->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($manager)->patchJson(route('bills.void', $bill));

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'voided');

        $this->assertDatabaseHas('bills', [
            'id' => $bill->id,
            'status' => 'voided',
        ]);
    });

    test('cashier cannot void a bill', function () {
        $cashier = User::factory()->withRole('cashier')->create();
        $bill = Bill::factory()->create(['status' => 'open']);

        $response = $this->actingAs($cashier)->patchJson(route('bills.void', $bill));

        $response->assertStatus(403);
    });

    test('waiter cannot void a bill', function () {
        $waiter = User::factory()->withRole('waiter')->create();
        $bill = Bill::factory()->create(['status' => 'open']);

        $response = $this->actingAs($waiter)->patchJson(route('bills.void', $bill));

        $response->assertStatus(403);
    });
});

describe('Authorization Roles', function () {
    test('admin has full access to all bill actions', function () {
        $admin = User::factory()->withRole('admin')->create();
        $session = TableSession::factory()->create();
        $order = Order::factory()->create(['session_id' => $session->id]);
        OrderItem::factory()->create(['order_id' => $order->id]);
        $bill = Bill::factory()->create(['session_id' => $session->id, 'status' => 'open']);

        $this->actingAs($admin)->getJson(route('bills.index'))->assertStatus(200);
        $this->actingAs($admin)->getJson(route('bills.show', $bill))->assertStatus(200);
        $this->actingAs($admin)->postJson(route('bills.store'), ['session_id' => $session->id])->assertStatus(201);
        $this->actingAs($admin)->patchJson(route('bills.void', $bill))->assertStatus(200);
    });
});
