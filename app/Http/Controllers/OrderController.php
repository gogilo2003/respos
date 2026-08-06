<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\MenuItem;
use App\Models\TableSession;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'table_session_id' => 'required|exists:table_sessions,id',
            'items' => 'required|array',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $session = TableSession::find($request->table_session_id);
        if (! $session || $session->status !== 'open') {
            return response()->json(['error' => 'Invalid table session'], 400);
        }

        $order = DB::transaction(function () use ($request, $session) {
            $order = Order::create([
                'session_id' => $session->id,
                'placed_by_role' => auth()->user()->role->name,
                'placed_by_user' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $menuItem = MenuItem::find($item['menu_item_id']);
                if (! $menuItem) continue;

                $unitPrice = $menuItem->base_price;
                $quantity = $item['quantity'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
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