<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableSession extends Model
{
    protected $table = 'table_sessions';

    protected $fillable = [
        'table_id',
        'session_token',
        'opened_by',
        'open_source',
        'status',
        'customer_count',
        'notes',
        'token_expires_at',
        'closed_by',
        'close_reason',
    ];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'datetime',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function table()
    {
        return $this->belongsTo(RestaurantTable::class, 'table_id');
    }

    public function openedBy()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'session_id');
    }

    public function bills()
    {
        return $this->hasMany(Bill::class, 'session_id');
    }

    public function assistanceRequests()
    {
        return $this->hasMany(AssistanceRequest::class, 'session_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'session_id');
    }
}
