<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    protected $table = 'qr_codes';

    protected $fillable = [
        'table_id',
        'payload',
        'image_path',
        'regenerated_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'regenerated_at' => 'datetime',
        ];
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }
}
