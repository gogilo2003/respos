<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'bill_id',
        'split_id',
        'cashier_id',
        'payment_method',
        'manual_note',
        'amount_due',
        'amount_received',
        'change_due',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_received' => 'decimal:2',
            'change_due' => 'decimal:2',
            'confirmed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function split()
    {
        return $this->belongsTo(BillSplit::class, 'split_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function refund()
    {
        return $this->hasOne(Refund::class, 'payment_id');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }
}
