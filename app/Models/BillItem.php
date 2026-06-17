<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $table = 'bill_items';

    protected $fillable = [
        'bill_id',
        'order_item_id',
        'quantity',
        'unit_price',
        'line_total',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'line_total' => 'decimal:2',
            'served_at' => 'datetime',
        ];
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function splitItems()
    {
        return $this->hasMany(BillSplitItem::class, 'bill_item_id');
    }
}
