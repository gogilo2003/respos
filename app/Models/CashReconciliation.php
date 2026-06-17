<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashReconciliation extends Model
{
    protected $table = 'cash_reconciliations';

    protected $fillable = [
        'reconciliation_date',
        'prepared_by',
        'approved_by',
        'system_total',
        'physical_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'reconciliation_date' => 'date',
            'system_total' => 'decimal:2',
            'physical_count' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getVarianceAmountAttribute()
    {
        return $this->physical_count - $this->system_total;
    }

    public function getVariancePctAttribute()
    {
        return $this->system_total == 0 ? 0 : (($this->physical_count - $this->system_total) / $this->system_total) * 100;
    }

    public function getFlaggedAttribute()
    {
        return abs($this->variance_pct) > 0.5;
    }

    public function isFlagged()
    {
        return $this->flagged;
    }

    public function scopeToday($query)
    {
        return $query->where('reconciliation_date', today()->toDateString());
    }
}
