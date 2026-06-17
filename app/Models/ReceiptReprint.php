<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptReprint extends Model
{
    protected $table = 'receipt_reprints';

    public $timestamps = false;

    protected $fillable = [
        'receipt_id',
        'reprinted_by',
    ];

    protected function casts(): array
    {
        return [
            'reprinted_at' => 'datetime',
        ];
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id');
    }

    public function reprintedBy()
    {
        return $this->belongsTo(User::class, 'reprinted_by');
    }
}
