<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillSplit extends Model
{
    protected $table = 'bill_splits';

    protected $fillable = [
        'bill_id',
        'split_type',
        'split_label',
        'amount_due',
        'amount_paid',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function items()
    {
        return $this->hasMany(BillSplitItem::class, 'split_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'split_id');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOutstanding($query)
    {
        return $query->where('status', 'outstanding');
    }

    public function getAmountRemainingAttribute()
    {
        return $this->amount_due - $this->amount_paid;
    }
}
