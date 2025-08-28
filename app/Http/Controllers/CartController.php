<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->cart()->with('product')->get();

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $items->each(function ($item) {
                $item->product->makeHidden('buying_price');
            });
        }

        $subtotal = $items->sum(fn($item) => $item->product->selling_price * $item->quantity);
        $shipping = $items->isEmpty() ? 0 : 3;
        $total = $subtotal + $shipping;

        return response()->json([
            'success' => true,
            'items' => $items,
            'summary' => [
                'subtotal' => number_format($subtotal, 2),
                'shipping' => number_format($shipping, 2),
                'total' => number_format($total, 2),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = Cart::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $request->product_id,
            ],
            ['quantity' => $request->quantity]
        );

        $cartItem->load('product');

        if (!auth('sanctum')->user() || auth('sanctum')->user()->role_id !== 1) {
            $cartItem->product->makeHidden('buying_price');
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart',
            'cart' => $cartItem
        ], 201);
    }

    public function update(Request $request, $product_id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->first();

        if (! $cart) {
            return response()->json(['success' => false, 'message' => 'Product not found in cart'], 404);
        }

        $cart->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated',
            'cart' => $cart
        ]);
    }

    public function destroy(Request $request, $product_id)
    {
        $cart = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product_id)
            ->first();

        if (! $cart) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found in cart'
            ], 404);
        }

        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart'
        ]);
    }
}
