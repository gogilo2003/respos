<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MenuItemSeeder extends Seeder
{
    /**
     * Seed menu items with images downloaded from free stock sources.
     */
    public function run(): void
    {
        $breakfast = MenuCategory::where('name', 'Breakfast')->first();
        $mainMeals = MenuCategory::where('name', 'Main Meals')->first();
        $beverages = MenuCategory::where('name', 'Beverages')->first();
        $desserts = MenuCategory::where('name', 'Desserts')->first();

        $items = [
            [
                'category_id' => $breakfast?->id,
                'name' => 'Buttermilk Pancakes',
                'description' => 'Fluffy pancakes served with maple syrup.',
                'base_price' => 320.00,
                'tax_inclusive' => true,
                'prep_time_min' => 12,
                'sort_order' => 1,
                'image_filename' => 'buttermilk-pancakes.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $breakfast?->id,
                'name' => 'Avocado Toast',
                'description' => 'Toasted sourdough with smashed avocado.',
                'base_price' => 280.00,
                'tax_inclusive' => true,
                'prep_time_min' => 10,
                'sort_order' => 2,
                'image_filename' => 'avocado-toast.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1541519227354-08fa2d2c914e?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $mainMeals?->id,
                'name' => 'Grilled Chicken',
                'description' => 'Juicy grilled chicken with herbs.',
                'base_price' => 450.00,
                'tax_inclusive' => true,
                'prep_time_min' => 20,
                'sort_order' => 1,
                'image_filename' => 'grilled-chicken.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1598515214211-89d3c73ae83b?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $mainMeals?->id,
                'name' => 'Beef Stew',
                'description' => 'Slow-cooked beef stew with vegetables.',
                'base_price' => 420.00,
                'tax_inclusive' => true,
                'prep_time_min' => 25,
                'sort_order' => 2,
                'image_filename' => 'beef-stew.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $beverages?->id,
                'name' => 'Fresh Orange Juice',
                'description' => 'Cold-pressed orange juice.',
                'base_price' => 180.00,
                'tax_inclusive' => true,
                'prep_time_min' => 5,
                'sort_order' => 1,
                'image_filename' => 'orange-juice.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $beverages?->id,
                'name' => 'Iced Coffee',
                'description' => 'Chilled coffee with smooth taste.',
                'base_price' => 200.00,
                'tax_inclusive' => true,
                'prep_time_min' => 5,
                'sort_order' => 2,
                'image_filename' => 'iced-coffee.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $desserts?->id,
                'name' => 'Chocolate Cake',
                'description' => 'Rich chocolate layer cake.',
                'base_price' => 350.00,
                'tax_inclusive' => true,
                'prep_time_min' => 10,
                'sort_order' => 1,
                'image_filename' => 'chocolate-cake.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'category_id' => $desserts?->id,
                'name' => 'Vanilla Ice Cream',
                'description' => 'Creamy vanilla ice cream scoop.',
                'base_price' => 220.00,
                'tax_inclusive' => true,
                'prep_time_min' => 3,
                'sort_order' => 2,
                'image_filename' => 'vanilla-ice-cream.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1497034825429-c343d7c6a68f?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        foreach ($items as $item) {
            $filename = $item['image_filename'];
            $localPath = 'menu-items/'.$filename;
            $contents = @file_get_contents($item['image_url']);

            if ($contents !== false) {
                Storage::disk('public')->put($localPath, $contents);
            }

            MenuItem::updateOrCreate(
                [
                    'category_id' => $item['category_id'],
                    'name' => $item['name'],
                ],
                [
                    'description' => $item['description'],
                    'base_price' => $item['base_price'],
                    'tax_inclusive' => $item['tax_inclusive'],
                    'prep_time_min' => $item['prep_time_min'],
                    'sort_order' => $item['sort_order'],
                    'image_url' => Storage::url($localPath),
                    'modifier_groups' => null,
                    'is_available' => true,
                ]
            );
        }
    }
}
