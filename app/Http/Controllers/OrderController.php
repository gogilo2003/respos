<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Interfaces\Repositories\OrderRepositoryInterface;
use App\Models\MenuItem;
use App\Models\TableSession;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderRepositoryInterface $orderRepository)
    {
    }

    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $session = TableSession::find($validated['table_session_id']);
        if (! $session || $session->status !== 'open') {
            return response()->json(['error' => 'Invalid table session'], 400);
        }

        $order = DB::transaction(function () use ($request, $session, $validated) {
            $order = $this->orderRepository->createOrder([
                'session_id' => $session->id,
                'placed_by_role' => auth()->user()->role->name,
                'placed_by_user' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                if (! $menuItem) continue;

                $this->orderRepository->addOrderItem([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->base_price,
                ]);
            }

            return $order;
        });

        return response()->json([
            'order_id' => $order->id,
            'status' => 'created',
        ]);
    }
}