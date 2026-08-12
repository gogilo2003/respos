<?php

use App\Models\Bill;
use App\Models\CashReconciliation;
use App\Models\Payment;
use App\Models\TableSession;
use App\Models\User;

test('cashier can view reconciliations page without query error', function () {
    $cashier = User::factory()->withRole('cashier')->create();
    $session = TableSession::factory()->create();
    $bill = Bill::factory()->create(['session_id' => $session->id]);

    Payment::create([
        'bill_id' => $bill->id,
        'cashier_id' => $cashier->id,
        'payment_method' => 'cash',
        'amount_due' => 125.50,
        'amount_received' => 130.00,
        'change_due' => 4.50,
        'status' => 'confirmed',
    ]);

    $response = $this->actingAs($cashier)->get(route('reconciliations.index'));

    $response->assertStatus(200);
});

test('cashier can submit daily cash count reconciliation', function () {
    $cashier = User::factory()->withRole('cashier')->create();

    $response = $this->actingAs($cashier)->post(route('reconciliations.store'), [
        'reconciliation_date' => today()->toDateString(),
        'physical_count' => 450.00,
        'notes' => 'Evening shift closeout',
    ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('cash_reconciliations', [
        'prepared_by' => $cashier->id,
        'physical_count' => 450.00,
    ]);
});

test('admin can approve flagged reconciliation', function () {
    $admin = User::factory()->withRole('admin')->create();
    $reconciliation = CashReconciliation::create([
        'reconciliation_date' => today()->toDateString(),
        'prepared_by' => $admin->id,
        'system_total' => 500.00,
        'physical_count' => 400.00,
    ]);

    $response = $this->actingAs($admin)->post(route('reconciliations.approve', $reconciliation->id));

    $response->assertStatus(302);
    $this->assertDatabaseHas('cash_reconciliations', [
        'id' => $reconciliation->id,
        'approved_by' => $admin->id,
    ]);
});
