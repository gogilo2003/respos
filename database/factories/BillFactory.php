<?php

namespace Database\Factories;

use App\Models\Bill;
use App\Models\TableSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bill>
 */
class BillFactory extends Factory
{
    protected $model = Bill::class;

    public function definition(): array
    {
        return [
            'bill_number' => 'BILL-' . date('Y') . '-' . str_pad((string) fake()->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'session_id' => TableSession::factory(),
            'generated_by' => User::factory()->withRole('cashier'),
            'status' => 'draft',
            'subtotal' => 30.00,
            'grand_total' => 30.00,
            'generated_at' => now(),
        ];
    }
}
