<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerOrderController extends Controller
{
    public function track(Request $request, Order $order)
    {
        $order->load(['items.menuItem', 'session.table']);

        return Inertia::render('Orders/Track', [
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
                'table_number' => $order->session?->table?->table_number ?? 'N/A',
                'placed_at' => $order->created_at->format('H:i, M d'),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->menuItem?->name ?? 'Menu Item',
                    'quantity' => $item->quantity,
                    'unit_price' => (float) $item->unit_price,
                    'total_price' => (float) ($item->quantity * $item->unit_price),
                    'special_instructions' => $item->special_instructions,
                    'selected_modifiers' => $item->selected_modifiers,
                    'status' => $item->status,
                ]),
                'total_amount' => (float) $order->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            ],
        ]);
    }
}
