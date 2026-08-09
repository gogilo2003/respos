<?php

namespace Database\Factories;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'category_id' => MenuCategory::factory(),
            'name' => fake()->word(),
            'base_price' => 15.00,
            'tax_inclusive' => true,
            'prep_time_min' => 10,
            'is_available' => true,
        ];
    }
}
