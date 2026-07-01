<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_item_id' => ['required', 'integer', 'exists:menu_items,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $validator->validate();

        $menuItem = MenuItem::query()->findOrFail($request->input('menu_item_id'));
        $quantity = (int) ($request->input('quantity', 1));

        $cart = $request->session()->get('cart', []);

        $id = (string) $menuItem->id;

        if (! isset($cart[$id])) {
            $cart[$id] = [
                'menu_item_id' => $menuItem->id,
                'title' => $menuItem->name,
                'description' => $menuItem->description,
                'image' => $menuItem->image_url,
                'unit_price' => (float) $menuItem->base_price,
                'quantity' => 0,
            ];
        }

        $cart[$id]['quantity'] += $quantity;

        $request->session()->put('cart', $cart);

        $cartCount = array_sum(array_map(fn ($row) => (int) $row['quantity'], $cart));

        return response()->json([
            'ok' => true,
            'cartCount' => $cartCount,
        ]);
    }
}
