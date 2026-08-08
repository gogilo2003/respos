<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class MenuCategorySeeder extends Seeder
{
    /**
     * Seed categories with cover images downloaded from free stock sources.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Breakfast',
                'description' => 'Morning meals and brunch favorites.',
                'sort_order' => 1,
                'image_filename' => 'breakfast-category.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'name' => 'Main Meals',
                'description' => 'Hearty mains for lunch and dinner.',
                'sort_order' => 2,
                'image_filename' => 'main-meals-category.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'name' => 'Beverages',
                'description' => 'Fresh drinks, juices, and coffee.',
                'sort_order' => 3,
                'image_filename' => 'beverages-category.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=1200&q=80',
            ],
            [
                'name' => 'Desserts',
                'description' => 'Sweet treats to finish your meal.',
                'sort_order' => 4,
                'image_filename' => 'desserts-category.jpg',
                'image_url' => 'https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&w=1200&q=80',
            ],
        ];

        foreach ($categories as $category) {
            $filename = $category['image_filename'];
            $localPath = 'menu-items/'.$filename;
            $contents = @file_get_contents($category['image_url']);

            if ($contents !== false) {
                Storage::disk('public')->put($localPath, $contents);
            }

            MenuCategory::updateOrCreate(
                ['name' => $category['name']],
                [
                    'description' => $category['description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
