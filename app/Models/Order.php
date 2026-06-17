<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'session_id',
        'placed_by_role',
        'placed_by_user',
        'status',
        'cancel_reason',
        'cancelled_by',
        'accepted_at',
        'first_ready_at',
        'fully_served_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'placed_at' => 'datetime',
            'accepted_at' => 'datetime',
            'first_ready_at' => 'datetime',
            'fully_served_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }

    public function placedBy()
    {
        return $this->belongsTo(User::class, 'placed_by_user');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function billItems()
    {
        return $this->hasManyThrough(BillItem::class, OrderItem::class, 'order_id', 'order_item_id');
    }
}
