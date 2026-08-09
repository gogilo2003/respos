<?php

namespace Database\Factories;

use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TableSession>
 */
class TableSessionFactory extends Factory
{
    protected $model = TableSession::class;

    public function definition(): array
    {
        return [
            'table_id' => RestaurantTable::factory(),
            'session_token' => fake()->unique()->uuid(),
            'open_source' => 'waiter',
            'status' => 'open',
            'customer_count' => 2,
            'token_expires_at' => now()->addHours(2),
            'opened_at' => now(),
        ];
    }
}
