<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Interfaces\Repositories\AssistanceRequestRepositoryInterface;
use App\Interfaces\Repositories\MenuItemRepositoryInterface;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Interfaces\Repositories\TableRepositoryInterface;
use App\Interfaces\Repositories\TableSessionRepositoryInterface;
use App\Models\AssistanceRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Services\WaiterStatisticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WaiterController extends Controller
{
    public function __construct(
        protected TableRepositoryInterface $tableRepository,
        protected TableSessionRepositoryInterface $tableSessionRepository,
        protected OrderRepositoryInterface $orderRepository,
        protected AssistanceRequestRepositoryInterface $assistanceRequestRepository,
        protected MenuItemRepositoryInterface $menuItemRepository,
        protected WaiterStatisticsService $waiterStatisticsService,
        protected OrderService $orderService,
    ) {}

    public function dashboard()
    {
        Gate::authorize('waiter', Order::class);

        $tables = $this->tableRepository->getActiveTablesWithSessions()->map(function ($table) {
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

        $tableIds = $tables->pluck('table_id')->filter()->all();
        $sessionIds = $tables->flatMap(fn ($table) => $table['active_session'] ? [$table['active_session']['table_session_id']] : [])->all();

        $orders = $sessionIds ? $this->orderRepository->getOrdersBySessionIds($sessionIds) : collect();
        $assistanceRequests = $this->assistanceRequestRepository->getOpenRequests();

        $ordersPayload = $orders->map(function ($order) {
            return [
                'orderNumber' => $order->id,
                'table' => $order->session?->table?->table_number ?? 'Unknown',
                'customer' => null,
                'time' => $order->placed_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
                'status' => $order->status,
            ];
        });

        $assistancePayload = $assistanceRequests->map(function ($request) {
            return [
                'tableNumber' => $request->session?->table?->table_number ?? 'Unknown',
                'request' => $request->request_type ?? 'Assistance',
                'priority' => 'medium',
                'time' => $request->requested_at?->format('Y-m-d H:i') ?? now()->format('Y-m-d H:i'),
            ];
        });

        $statistics = $this->waiterStatisticsService->getDashboardStatistics($sessionIds);

        return Inertia::render('Waiter/Dashboard', [
            'tables' => $tables,
            'orders' => $ordersPayload,
            'statistics' => $statistics,
            'assistance_requests' => $assistancePayload,
        ]);
    }

    public function storeOrder(StoreOrderRequest $request)
    {
        Gate::authorize('waiter', Order::class);

        $validated = $request->validated();

        $session = $this->tableSessionRepository->find($validated['table_session_id']);
        if (! $session || $session->status !== 'open') {
            return response()->json(['error' => 'Session is not open'], 422);
        }

        $order = DB::transaction(function () use ($validated, $session) {
            $order = $this->orderRepository->createOrder([
                'session_id' => $session->id,
                'placed_by_role' => 'waiter',
                'placed_by_user' => auth()->id(),
                'status' => 'accepted',
            ]);

            foreach ($validated['items'] as $item) {
                $menuItem = $this->menuItemRepository->find($item['menu_item_id']);

                $this->orderRepository->addOrderItem([
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
        Gate::authorize('waiter', Order::class);

        $status = $request->query('status', 'open');

        $requests = $status === 'all'
            ? $this->assistanceRequestRepository->getRequestsByStatus('all')
            : $this->assistanceRequestRepository->getRequestsByStatus($status);

        return response()->json(['requests' => $requests]);
    }

    public function updateAssistance(Request $request, AssistanceRequest $assistanceRequest)
    {
        Gate::authorize('waiter', Order::class);

        $validated = $request->validate([
            'status' => 'required|in:pending,assigned,resolved',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->assistanceRequestRepository->update($assistanceRequest->id, [
            'status' => $validated['status'],
            'handled_by' => auth()->id(),
            'handled_at' => $validated['status'] === 'resolved' ? now() : null,
        ]);

        return response()->json(['request' => $assistanceRequest->fresh()]);
    }

    public function serveOrderItem(OrderItem $orderItem)
    {
        Gate::authorize('waiter', Order::class);

        $order = $orderItem->order;
        $session = $order->session;

        if (! $session || $session->status !== 'open') {
            return response()->json(['error' => 'Session not active'], 422);
        }

        if ($orderItem->status !== 'ready') {
            return response()->json(['error' => 'Only ready items can be marked as served'], 422);
        }

        DB::transaction(function () use ($orderItem, $order) {
            $orderItem->update([
                'status' => 'served',
                'served_at' => now(),
            ]);

            $order->load('items');

            $allServed = $order->items->every(
                fn ($i) => in_array($i->status, ['served', 'cancelled', 'voided'])
            );

            if ($allServed) {
                $this->orderService->markOrderFullyServed($order);
            }
        });

        return response()->json(['item' => $orderItem->fresh()]);
    }
}
