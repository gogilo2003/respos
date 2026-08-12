<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use App\Services\NotificationService;
use App\Services\OrderTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function __construct(
        protected OrderTransitionService $transitionService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Order::class);

        $query = Order::with(['session.table', 'items.menuItem', 'placedBy'])
            ->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('session.table', function ($tq) use ($search) {
                        $tq->where('table_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items.menuItem', function ($iq) use ($search) {
                        $iq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('table_id')) {
            $query->whereHas('session', function ($q) use ($request) {
                $q->where('table_id', $request->table_id);
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Active table sessions for order creation
        $activeSessions = TableSession::with('table')
            ->where('status', 'open')
            ->get()
            ->map(fn ($session) => [
                'id' => $session->id,
                'table_number' => $session->table?->table_number ?? 'Table #'.$session->table_id,
            ]);

        // Available menu items for order creation/editing
        $menuItems = MenuItem::where('is_available', true)
            ->with('category')
            ->orderBy('name')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'price' => (float) $item->base_price,
                'category_name' => $item->category?->name,
            ]);

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'status' => $request->status ?? 'all',
                'search' => $request->search ?? '',
                'table_id' => $request->table_id ?? '',
            ],
            'activeSessions' => $activeSessions,
            'menuItems' => $menuItems,
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Order::class);

        $validated = $request->validate([
            'table_session_id' => 'required|exists:table_sessions,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.special_instructions' => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated, $request) {
            $order = Order::create([
                'session_id' => $validated['table_session_id'],
                'placed_by_role' => $request->user()->role?->name ?? 'waiter',
                'placed_by_user' => $request->user()->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->base_price,
                    'special_instructions' => $item['special_instructions'] ?? null,
                    'status' => 'pending',
                ]);
            }

            return $order;
        });

        app(NotificationService::class)->notifyRole('kitchen', 'new_order_placed', $order->session_id, [
            'order_id' => $order->id,
        ]);

        return redirect()->back()->with('message', "Order #{$order->id} created successfully.");
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);

        $order->load(['session.table', 'items.menuItem', 'placedBy']);

        return response()->json([
            'ok' => true,
            'order' => $order,
        ]);
    }

    public function addItem(Request $request, Order $order)
    {
        Gate::authorize('update', $order);

        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'quantity' => 'required|integer|min:1',
            'special_instructions' => 'nullable|string',
        ]);

        $menuItem = MenuItem::findOrFail($validated['menu_item_id']);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'menu_item_id' => $menuItem->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $menuItem->base_price,
            'special_instructions' => $validated['special_instructions'] ?? null,
            'status' => 'pending',
        ]);

        app(NotificationService::class)->notifyRole('kitchen', 'order_item_added', $order->session_id, [
            'order_id' => $order->id,
            'item_id' => $orderItem->id,
        ]);

        return redirect()->back()->with('message', "Added {$menuItem->name} to Order #{$order->id}.");
    }

    public function updateItem(Request $request, Order $order, OrderItem $item)
    {
        Gate::authorize('update', $order);

        if ($item->order_id !== $order->id) {
            abort(404);
        }

        $validated = $request->validate([
            'quantity' => 'sometimes|required|integer|min:1',
            'special_instructions' => 'nullable|string',
            'status' => 'sometimes|required|string|in:pending,preparing,ready,served,cancelled',
        ]);

        $item->update($validated);

        return redirect()->back()->with('message', "Updated order item #{$item->id}.");
    }

    public function removeItem(Order $order, OrderItem $item)
    {
        Gate::authorize('update', $order);

        if ($item->order_id !== $order->id) {
            abort(404);
        }

        $item->delete();

        return redirect()->back()->with('message', "Item removed from Order #{$order->id}.");
    }

    public function transition(Request $request, Order $order)
    {
        Gate::authorize('transition', $order);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,accepted,preparing,ready,served,completed,cancelled',
        ]);

        $updatedOrder = $this->transitionService->transition($order, $validated['status']);

        // Dispatch notifications to waiter & customer
        app(NotificationService::class)->notifyRole('waiter', 'order_status_updated', $order->session_id, [
            'order_id' => $order->id,
            'status' => $validated['status'],
        ]);
        app(NotificationService::class)->notifyRole('customer', 'order_status_updated', $order->session_id, [
            'order_id' => $order->id,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'order_id' => $updatedOrder->id,
                'status' => $updatedOrder->status,
            ]);
        }

        return redirect()->back()->with('message', "Order #{$order->id} status updated to {$validated['status']}.");
    }
}
