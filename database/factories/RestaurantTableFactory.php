<?php

namespace Database\Factories;

use App\Models\RestaurantTable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantTable>
 */
class RestaurantTableFactory extends Factory
{
    protected $model = RestaurantTable::class;

    public function definition(): array
    {
        return [
            'table_number' => 'T'.fake()->unique()->numberBetween(1, 9999),
            'capacity' => 4,
            'location' => 'Main Floor',
            'status' => 'available',
            'is_active' => true,
        ];
    }
}
