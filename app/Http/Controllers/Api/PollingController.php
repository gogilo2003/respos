<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssistanceRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PollingController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function updates(Request $request)
    {
        $user = $request->user();
        $since = $request->query('since');

        $updates = $this->notificationService->getUpdatesForUser($user, $since);

        // Include role-specific counts for quick dashboard syncing
        $role = $user?->role?->name ?? 'customer';

        $summary = [];
        if ($role === 'kitchen') {
            $summary['pending_items_count'] = OrderItem::where('status', 'pending')->count();
            $summary['preparing_items_count'] = OrderItem::where('status', 'preparing')->count();
        } elseif ($role === 'waiter') {
            $summary['active_orders_count'] = Order::whereIn('status', ['pending', 'accepted', 'preparing', 'ready'])->count();
            $summary['ready_orders_count'] = Order::where('status', 'ready')->count();
            $summary['open_assistance_count'] = AssistanceRequest::where('status', 'pending')->count();
        } elseif ($role === 'cashier' || $role === 'admin' || $role === 'manager') {
            $summary['active_sessions_count'] = TableSession::where('status', 'open')->count();
            $summary['pending_bills_count'] = Order::where('status', 'served')->count();
        }

        return response()->json([
            'ok' => true,
            'summary' => $summary,
            'notifications' => $updates['notifications'],
            'unread_count' => $updates['unread_count'],
            'server_time' => $updates['server_time'],
        ]);
    }

    public function markRead(Request $request, int $id)
    {
        $success = $this->notificationService->markAsRead($id);

        return response()->json(['ok' => $success]);
    }
}
