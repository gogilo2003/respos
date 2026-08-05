<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Services\MenuService;

class HomeController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function welcome()
    {
        $menuItems = $this->menuService->getMenuItems();

        return Inertia::render('Welcome', ['menuItems' => $menuItems]);
    }

    public function categories()
    {
        return Inertia::render('Categories', ['categories' => $this->menuService->getMenuCategories()]);
    }

    public function menu()
    {
        $menuItems = $this->menuService->getMenuItems();

        return Inertia::render('Menu', ['menuItems' => $menuItems]);
    }

    public function about()
    {
        return Inertia::render('About');
    }

    public function cart()
    {
        return Inertia::render('Cart');
    }

    public function completeOrder(Request $request)
    {
        $request->validate([
            'session_id' => ['required', 'exists:table_sessions,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $session = TableSession::findOrFail($request->session_id);
        if ($session->status !== 'open') {
            return response()->json(['message' => 'Session is not open'], 422);
        }

        $order = DB::transaction(function () use ($request, $session) {
            $order = Order::create([
                'session_id' => $session->id,
                'placed_by_role' => 'customer',
                'placed_by_user' => null,
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $unitPrice = $menuItem->base_price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                ]);
            }

            return $order;
        });

        return response()->json(['order_id' => $order->id, 'status' => $order->status]);
    }
}