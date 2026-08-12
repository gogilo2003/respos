<?php

namespace App\Http\Controllers;

use App\Http\Requests\KitchenUpdateItemRequest;
use App\Interfaces\Repositories\KitchenRepositoryInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\KitchenDashboardService;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class KitchenController extends Controller
{
    public function __construct(
        protected KitchenDashboardService $kitchenDashboardService,
        protected KitchenRepositoryInterface $kitchenRepository,
        protected OrderService $orderService,
    ) {}

    public function dashboard()
    {
        Gate::authorize('kitchen', Order::class);

        return Inertia::render('Kitchen/Dashboard', $this->kitchenDashboardService->getDashboardData());
    }

    public function updateItemStatus(KitchenUpdateItemRequest $request, OrderItem $orderItem)
    {
        Gate::authorize('kitchen', Order::class);

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

            $order = $this->kitchenRepository->refreshOrderItems($order);

            $allReady = $order->items->every(fn ($i) => in_array($i->status, ['ready', 'served']));
            $anyPreparing = $order->items->contains(fn ($i) => $i->status === 'preparing');

            if ($allReady) {
                $this->orderService->markOrderReady($order);
            } elseif ($anyPreparing) {
                $this->kitchenRepository->setOrderStatus($order, 'preparing');
            }
        });

        return response()->json(['item' => $orderItem->fresh()]);
    }

    public function markOrderReady(Order $order)
    {
        Gate::authorize('kitchen', Order::class);

        $session = $order->session;

        if (! $session || $session->status !== 'open') {
            return response()->json(['error' => 'Session not active'], 422);
        }

        $order = $this->kitchenRepository->refreshOrderItems($order);

        $allItemsReady = $order->items->every(
            fn ($i) => in_array($i->status, ['ready', 'served'])
        );

        if (! $allItemsReady) {
            return response()->json(['error' => 'Not all items are ready'], 422);
        }

        $this->orderService->markOrderReady($order);

        return response()->json(['order' => $order->fresh()]);
    }
}
