<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssistanceRequest extends Model
{
    protected $table = 'assistance_requests';

    protected $fillable = [
        'session_id',
        'request_type',
        'handled_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'handled_at' => 'datetime',
        ];
    }

    public function session()
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }

    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeHandled($query)
    {
        return $query->where('status', 'handled');
    }
}
