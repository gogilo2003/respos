<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use App\Services\MenuService;
use App\Services\NotificationService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HomeController extends Controller
{
    protected $menuService;

    public function __construct(MenuService $menuService)
    {
        $this->menuService = $menuService;
    }

    public function welcome(Request $request)
    {
        $menuItems = $this->menuService->getMenuItems($request->user());

        return Inertia::render('Welcome', ['menuItems' => $menuItems]);
    }

    public function categories(Request $request)
    {
        return Inertia::render('Categories', ['categories' => $this->menuService->getMenuCategories($request->user())]);
    }

    public function menu(Request $request)
    {
        $menuItems = $this->menuService->getMenuItems($request->user());

        return Inertia::render('Menu', ['menuItems' => $menuItems]);
    }

    public function about()
    {
        return Inertia::render('About');
    }

    public function cart()
    {
        $tables = RestaurantTable::where('is_active', true)
            ->orderBy('table_number')
            ->get(['id', 'table_number', 'location']);

        $activeSessionId = session('active_session_id');
        $activeSession = null;
        if ($activeSessionId) {
            $session = TableSession::with('table')->find($activeSessionId);
            if ($session && $session->status === 'open') {
                $activeSession = [
                    'id' => $session->id,
                    'table_id' => $session->table_id,
                    'table_number' => $session->table?->table_number ?? 'N/A',
                ];
            }
        }

        return Inertia::render('Cart', [
            'tables' => $tables,
            'activeSession' => $activeSession,
        ]);
    }

    public function completeOrder(Request $request)
    {
        $sessionId = $request->input('session_id') ?: session('active_session_id');
        $tableId = $request->input('table_id');

        if (! $sessionId && $tableId) {
            $session = app(QrCodeService::class)->getOrCreateSession((int) $tableId, 'customer_qr');
            $sessionId = $session->id;
            session(['active_session_id' => $sessionId]);
        }

        if (! $sessionId) {
            return response()->json(['message' => 'No active table session found. Please select a table or scan table QR code.'], 422);
        }

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'exists:menu_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.selected_modifiers' => ['nullable', 'array'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:120'],
        ]);

        $session = TableSession::findOrFail($sessionId);
        if ($session->status !== 'open') {
            return response()->json(['message' => 'Session is not open'], 422);
        }

        $order = DB::transaction(function () use ($request, $session) {
            $order = Order::create([
                'session_id' => $session->id,
                'placed_by_role' => 'customer',
                'placed_by_user' => auth()->id(),
                'status' => 'pending',
            ]);

            foreach ($request->items as $item) {
                $menuItem = MenuItem::findOrFail($item['menu_item_id']);
                $unitPrice = (float) $menuItem->base_price;

                // Add extra price for selected modifiers if any
                $modifiers = $item['selected_modifiers'] ?? [];
                $extraPrice = 0;
                foreach ($modifiers as $mod) {
                    if (isset($mod['price'])) {
                        $extraPrice += (float) $mod['price'];
                    }
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice + $extraPrice,
                    'selected_modifiers' => $modifiers,
                    'special_instructions' => substr($item['special_instructions'] ?? '', 0, 120),
                    'status' => 'pending',
                ]);
            }

            return $order;
        });

        // Clear cart session
        $request->session()->forget('cart');

        // Dispatch notifications for kitchen and waiter
        app(NotificationService::class)->notifyRole('kitchen', 'order_placed', $session->id, [
            'order_id' => $order->id,
            'table_number' => $session->table?->table_number ?? 'N/A',
        ]);
        app(NotificationService::class)->notifyRole('waiter', 'order_placed', $session->id, [
            'order_id' => $order->id,
            'table_number' => $session->table?->table_number ?? 'N/A',
        ]);

        return response()->json([
            'ok' => true,
            'order_id' => $order->id,
            'status' => $order->status,
            'track_url' => route('orders.track', $order->id),
        ]);
    }
}
