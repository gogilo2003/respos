<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'special_instructions',
        'modifiers',
        'status',
        'accepted_at',
        'preparing_at',
        'ready_at',
        'served_at',
        'cancelled_at',
        'void_reason',
        'voided_by',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'modifiers' => 'array',
            'accepted_at' => 'datetime',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function billItems()
    {
        return $this->hasMany(BillItem::class, 'order_item_id');
    }

    public function scopeServed($query)
    {
        return $query->where('status', 'served');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled', 'voided']);
    }
}
