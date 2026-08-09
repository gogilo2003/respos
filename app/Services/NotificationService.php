<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Dispatch a role notification event.
     */
    public function notifyRole(
        string $targetRole,
        string $eventType,
        ?int $sessionId = null,
        ?array $payload = null,
        ?int $targetUserId = null
    ): Notification {
        return Notification::create([
            'target_role' => $targetRole,
            'target_user_id' => $targetUserId,
            'session_id' => $sessionId,
            'event_type' => $eventType,
            'payload' => $payload,
            'is_read' => false,
        ]);
    }

    /**
     * Get unread notifications for a user/role since a given timestamp.
     */
    public function getUpdatesForUser(?User $user, ?string $since = null): array
    {
        $role = $user?->role?->name ?? 'customer';

        $query = Notification::where('is_read', false)
            ->where(function ($q) use ($role, $user) {
                $q->where('target_role', $role);
                if ($user) {
                    $q->orWhere('target_user_id', $user->id);
                }
            });

        if ($since) {
            $query->where('created_at', '>', $since);
        }

        $notifications = $query->orderBy('created_at', 'desc')->take(20)->get();

        return [
            'unread_count' => Notification::where('is_read', false)
                ->where(function ($q) use ($role, $user) {
                    $q->where('target_role', $role);
                    if ($user) {
                        $q->orWhere('target_user_id', $user->id);
                    }
                })->count(),
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'target_role' => $n->target_role,
                'event_type' => $n->event_type,
                'session_id' => $n->session_id,
                'payload' => $n->payload,
                'created_at' => $n->created_at->toIso8601String(),
                'time_ago' => $n->created_at->diffForHumans(),
            ]),
            'server_time' => now()->toIso8601String(),
        ];
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $id): bool
    {
        $notification = Notification::find($id);
        if ($notification) {
            $notification->update(['is_read' => true]);

            return true;
        }

        return false;
    }
}
