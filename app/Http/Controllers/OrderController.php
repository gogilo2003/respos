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

        $order = Order::create([
            'table_session_id' => $session->id,
        ]);

        foreach ($request->items as $item) {
            $menuItem = MenuItem::find($item['menu_item_id']);
            if (! $menuItem) continue;

            $unitPrice = $menuItem->price;
            $quantity = $item['quantity'];

            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'menu_item_id' => $menuItem->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);
        }

        return response()->json([
            'order_id' => $order->id,
            'status' => 'created',
        ]);
    }
}