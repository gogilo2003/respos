<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantTable extends Model
{
    protected $table = 'restaurant_tables';

    protected $fillable = [
        'table_number',
        'capacity',
        'location',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'capacity' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class, 'table_id');
    }

    public function sessions()
    {
        return $this->hasMany(TableSession::class, 'table_id');
    }

    public function activeSession()
    {
        return $this->hasOne(TableSession::class, 'table_id')->whereIn('status', ['open', 'billing'])->latest();
    }
}
