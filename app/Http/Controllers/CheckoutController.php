<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
            'payment_code' => 'required|in:cash,whish',
        ]);

        $user = $request->user();
        $cartItems = $user->cart()->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 400);
        }

        $subtotal = $cartItems->sum(fn($item) => $item->product->selling_price * $item->quantity);
        $shipping = 3;
        $total = $subtotal + $shipping;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $user->id,
                'address_id' => $request->address_id,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $total,
                'payment_code' => $request->payment_code,
                'order_status' => 'pending',
                'date_added' => now(),
            ]);

            foreach ($cartItems as $item) {
                OrderProduct::create([
                    'order_id' => $order->order_id,
                    'product_id' => $item->product_id,
                    'price' => $item->product->selling_price,
                    'quantity' => $item->quantity,
                    'total' => $item->product->selling_price * $item->quantity,
                ]);

                $item->product->decrement('quantity', $item->quantity);
            }

            if ($request->payment_code == 'whish') {
                $whishResponse = $this->createWhishPayment($order);

                if ($whishResponse['status'] == true) {
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'payment_type' => 'whish',
                        'payment_url' => $whishResponse['data']['whishUrl'],
                        'order' => $order,
                    ]);
                } else {
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create Whish payment.',
                    ], 500);
                }
            } else {
                $user->cart()->delete();
                DB::commit();
                return response()->json([
                    'success' => true,
                    'payment_type' => 'cash',
                    'message' => 'Order placed successfully.',
                    'order' => $order->load('user', 'address'),
                ], 201);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function createWhishPayment($order)
    {
        $url = env('WHISH_BASE_URL') . '/payment/whish';

        $response = Http::withHeaders([
            'channel' => env('WHISH_CHANNEL'),
            'secret' => env('WHISH_SECRET'),
            'websiteurl' => env('APP_URL'),
        ])->post($url, [
            'amount' => $order->total,
            'currency' => 'USD',
            'invoice' => 'Order #' . $order->order_id,
            'externalId' => $order->order_id,
            'successCallbackUrl' => route('api.whish.callback.success', ['order_id' => $order->order_id]),
            'failureCallbackUrl' => route('api.whish.callback.failure', ['order_id' => $order->order_id]),
            'successRedirectUrl' => route('api.whish.redirect.success', ['order_id' => $order->order_id]),
            'failureRedirectUrl' => route('api.whish.redirect.failure', ['order_id' => $order->order_id]),
        ]);

        return $response->json();
    }
}
