<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class KitchenController extends Controller
{
    public function dashboard()
    {
        Gate::authorize('kitchen');

        $orders = Order::with(['session.table', 'items.menuItem'])
            ->whereHas('session', fn ($q) => $q->where('status', 'open'))
            ->whereHas('items', fn ($q) => $q->whereIn('status', ['pending', 'accepted', 'preparing']))
            ->orderBy('placed_at')
            ->get()
            ->map(function ($order) {
                $items = $order->items->map(function ($item) {
                    return [
                        'order_item_id' => $item->id,
                        'menu_item_id' => $item->menu_item_id,
                        'name' => $item->menuItem->name ?? null,
                        'quantity' => $item->quantity,
                        'status' => $item->status,
                        'unit_price' => $item->unit_price,
                        'sla_seconds_total' => $item->ready_at && $item->accepted_at
                            ? $item->ready_at->diffInSeconds($item->accepted_at)
                            : null,
                    ];
                });

                $counts = [
                    'pending' => $order->items->where('status', 'pending')->count(),
                    'accepted' => $order->items->where('status', 'accepted')->count(),
                    'preparing' => $order->items->where('status', 'preparing')->count(),
                    'ready' => $order->items->where('status', 'ready')->count(),
                ];

                return [
                    'order_id' => $order->id,
                    'session' => [
                        'table_session_id' => $order->session->id,
                        'table_number' => $order->session->table->table_number ?? null,
                    ],
                    'placed_at' => $order->placed_at,
                    'items' => $items,
                    'item_counts' => $counts,
                ];
            });

        return Inertia::render('Kitchen/Dashboard', ['orders' => $orders]);
    }

    public function updateItemStatus(UpdateOrderStatusRequest $request, OrderItem $orderItem)
    {
        Gate::authorize('kitchen');

        $validated = $request->validated();

        $order = $orderItem->order;
        $session = $order->session;

        if (! $session || $session->status !== 'open') {
            return response()->json(['error' => 'Session not active'], 422);
        }

        $currentStatus = $orderItem->status;
        $newStatus = $validated['status'];

        $validTransitions = [
            'pending' => ['accepted'],
            'accepted' => ['preparing'],
            'preparing' => ['ready'],
            'ready' => [],
        ];

        if (! in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
            return response()->json(['error' => 'Invalid status transition'], 422);
        }

        DB::transaction(function () use ($orderItem, $newStatus, $order) {
            $orderItem->update([
                'status' => $newStatus,
                'accepted_at' => $newStatus === 'accepted' ? now() : $orderItem->accepted_at,
                'preparing_at' => $newStatus === 'preparing' ? now() : $orderItem->preparing_at,
                'ready_at' => $newStatus === 'ready' ? now() : $orderItem->ready_at,
            ]);

            $allReady = $order->items->every(fn ($i) => in_array($i->status, ['ready', 'served']));
            $anyPreparing = $order->items->contains(fn ($i) => $i->status === 'preparing');

            if ($allReady) {
                $order->update(['status' => 'ready']);
            } elseif ($anyPreparing) {
                $order->update(['status' => 'preparing']);
            }
        });

        return response()->json(['item' => $orderItem->fresh()]);
    }
}