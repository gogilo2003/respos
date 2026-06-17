<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = 'bills';

    protected $fillable = [
        'session_id',
        'generated_by',
        'status',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'service_charge_rate',
        'service_charge_amount',
        'discount_amount',
        'discount_reason',
        'discount_approved_by',
        'grand_total',
        'paid_at',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'generated_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function discountApprovedBy()
    {
        return $this->belongsTo(User::class, 'discount_approved_by');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items()
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function splits()
    {
        return $this->hasMany(BillSplit::class, 'bill_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'bill_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'bill_id');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'partially_paid']);
    }
}
