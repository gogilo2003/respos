<?php

namespace App\Http\Controllers;

use App\Models\AssistanceRequest;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WaiterController extends Controller
{
    public function dashboard()
    {
        Gate::authorize('waiter');

        $tables = RestaurantTable::with(['activeSession.orders', 'activeSession.assistanceRequests'])
            ->where('is_active', true)
            ->get()
            ->map(function ($table) {
                $session = $table->activeSession;
                return [
                    'table_id' => $table->id,
                    'table_name' => $table->table_number,
                    'active_session' => $session ? [
                        'table_session_id' => $session->id,
                        'status' => $session->status,
                    ] : null,
                    'order_summary' => $session ? [
                        'active_orders_count' => $session->orders->count(),
                        'latest_order_status' => optional($session->orders->first())->status,
                    ] : null,
                    'assistance' => $session ? [
                        'open_requests_count' => $session->assistanceRequests->where('status', 'pending')->count(),
                    ] : null,
                ];
            });

        return Inertia::render('Waiter/Dashboard', ['tables' => $tables]);
    }

    public function storeOrder(Request $request)
    {
        Gate::authorize('waiter');

        $validated = $request->validate([
            'table_session_id' => 'required|exists:table_sessions,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $session = TableSession::findOrFail($validated['table_session_id']);
        if ($session->status !== 'open') {
            return response()->json(['error' => 'Session is not open'], 422);
        }

        $order = DB::transaction(function () use ($validated, $session) {
            $order = Order::create([
                'session_id' => $session->id,
                'placed_by_role' => 'waiter',
                'placed_by_user' => auth()->id(),
                'status' => 'accepted',
            ]);

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->base_price,
                ]);
            }

            return $order;
        });

        return response()->json(['order_id' => $order->id, 'status' => $order->status]);
    }

    public function assistanceRequests(Request $request)
    {
        Gate::authorize('waiter');

        $status = $request->query('status', 'open');

        $requests = AssistanceRequest::with('session.table')
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('requested_at')
            ->get();

        return response()->json(['requests' => $requests]);
    }

    public function updateAssistance(Request $request, AssistanceRequest $assistanceRequest)
    {
        Gate::authorize('waiter');

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,resolved',
            'notes' => 'nullable|string|max:255',
        ]);

        $assistanceRequest->update([
            'status' => $validated['status'],
            'handled_by' => auth()->id(),
            'handled_at' => $validated['status'] === 'resolved' ? now() : null,
        ]);

        return response()->json(['request' => $assistanceRequest]);
    }
}