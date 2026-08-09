<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'session_id' => TableSession::factory(),
            'placed_by_role' => 'waiter',
            'status' => 'pending',
            'placed_at' => now(),
        ];
    }
}
