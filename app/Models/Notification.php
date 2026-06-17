<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'target_role',
        'target_user_id',
        'session_id',
        'event_type',
        'payload',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_read' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function session()
    {
        return $this->belongsTo(TableSession::class, 'session_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForRole($query, string $role)
    {
        return $query->where('target_role', $role)->orWhere('target_user_id', auth()->id());
    }
}
