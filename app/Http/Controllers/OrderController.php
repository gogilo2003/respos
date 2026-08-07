<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function __construct(
        protected OrderTransitionService $transitionService
    ) {
    }

    public function transition(Request $request, Order $order)
    {
        Gate::authorize('transition', $order);

        $validated = $request->validate([
            'status' => 'required|string|in:pending,accepted,preparing,ready,served,completed,cancelled',
        ]);

        $updatedOrder = $this->transitionService->transition($order, $validated['status']);

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