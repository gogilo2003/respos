<?php

namespace App\Services;

class MenuService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getMenuItems()
    {
        // Dummy meals for now (replace with DB query later)
        $data = [
            [
                'id' => 1,
                'title' => 'Bread',
                'description' => 'Freshly baked bread served warm.',
                'image' => 'https://images.unsplash.com/photo-1549931319-a545dc4b1f3b?auto=format&fit=crop&w=800&q=60',
                'price' => 234,
            ],
            [
                'id' => 2,
                'title' => 'Cake',
                'description' => 'Soft and sweet cake slice with rich flavor.',
                'image' => 'https://images.unsplash.com/photo-1542826438-bd32f4e0d5a4?auto=format&fit=crop&w=800&q=60',
                'price' => 675,
            ],
            [
                'id' => 3,
                'title' => 'Beef Stew',
                'description' => 'Slow-cooked beef stew with vegetables.',
                'image' => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=800&q=60',
                'price' => 100,
            ],
            [
                'id' => 4,
                'title' => 'Chicken Salad',
                'description' => 'Crisp greens with grilled chicken and house dressing.',
                'image' => 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?auto=format&fit=crop&w=800&q=60',
                'price' => 250,
            ],
            [
                'id' => 5,
                'title' => 'Pasta Alfredo',
                'description' => 'Creamy Alfredo sauce with perfectly cooked pasta.',
                'image' => 'https://images.unsplash.com/photo-1523986371872-9d3ba2e2f642?auto=format&fit=crop&w=800&q=60',
                'price' => 320,
            ],
            [
                'id' => 6,
                'title' => 'Iced Coffee',
                'description' => 'Chilled coffee with a smooth, refreshing taste.',
                'image' => 'https://images.unsplash.com/photo-1511920170033-f8396924c348?auto=format&fit=crop&w=800&q=60',
                'price' => 180,
            ],
        ];

        return $data;

    }
}
