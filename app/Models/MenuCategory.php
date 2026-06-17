<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCategory extends Model
{
    protected $table = 'menu_categories';

    protected $fillable = [
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function menuItems()
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }

    public function availableItems()
    {
        return $this->hasMany(MenuItem::class, 'category_id')->where('is_available', true);
    }
}
