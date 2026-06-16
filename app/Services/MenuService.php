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
        $data =  [
            ['title' => 'Bread', 'description' => '', 'image' => '', 'price' => 234],
            ['title' => 'Cake', 'description' => '', 'image' => '', 'price' => 675],
            ['title' => 'Beef Stew', 'description' => '', 'image' => '', 'price' => 100],
        ];

        return $data;
    }
}
