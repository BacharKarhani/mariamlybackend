<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedUser;
use App\Mail\OrderPlacedAdmin;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'address_id'   => 'required|exists:addresses,id',
            'payment_code' => 'required|in:cash', // Cash only
        ]);

        $user = $request->user();
        $cartItems = $user->cart()->with(['product', 'variant'])->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 400);
        }

        $subtotal = $cartItems->sum(fn($item) => $item->product->selling_price * $item->quantity);
        
        // Get shipping price based on zone
        $shipping = \App\Models\Zone::getShippingPriceForAddress($request->address_id);
        
        $total    = $subtotal + $shipping;

        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id'      => $user->id,
                'address_id'   => $request->address_id,
                'subtotal'     => $subtotal,
                'shipping'     => $shipping,
                'total'        => $total,
                'payment_code' => $request->payment_code, // 'cash'
                'order_status' => 'pending',
                'date_added'   => now(),
            ]);

            foreach ($cartItems as $item) {
                OrderProduct::create([
                    'order_id'   => $order->order_id, // primary key عندك
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'price'      => $item->product->selling_price,
                    'quantity'   => $item->quantity,
                    'total'      => $item->product->selling_price * $item->quantity,
                ]);

                // تنزيل المخزون
                $item->product->decrement('quantity', $item->quantity);
            }

            // تفريغ السلة
            $user->cart()->delete();

            DB::commit();

            // حمّل العلاقات الصحيحة للإيميل
$orderFresh = Order::with(['user', 'address.zone', 'orderProducts.product', 'orderProducts.variant'])
                ->where('order_id', $order->order_id)
                ->first();

            // =============================
            // إرسال الإيميلات (لا تفشل الطلب)
            // =============================
            try {
                if (!empty($orderFresh->user?->email)) {
                    Log::info('Sending user email for order #'.$orderFresh->order_id);
                    Mail::to($orderFresh->user->email)
                        ->send(new OrderPlacedUser($orderFresh));
                    Log::info('User email sent successfully for order #'.$orderFresh->order_id);
                } else {
                    Log::warning('User email missing for order #'.$orderFresh->order_id);
                }

                $adminEmail = 'info@mariamly.com'; 
                if ($adminEmail) {
                    Log::info('Sending admin email for order #'.$orderFresh->order_id.' to '.$adminEmail);
                    Mail::to($adminEmail)->send(new OrderPlacedAdmin($orderFresh));
                    Log::info('Admin email sent successfully for order #'.$orderFresh->order_id);
                } else {
                    Log::warning('ADMIN_EMAIL not set; admin email skipped for order #'.$orderFresh->order_id);
                }
            } catch (\Throwable $mailEx) {
                Log::error('Order emails failed: ' . $mailEx->getMessage(), [
                    'order_id' => $order->order_id,
                    'trace' => $mailEx->getTraceAsString()
                ]);
            }

            return response()->json([
                'success'      => true,
                'payment_type' => 'cash',
                'message'      => 'Order placed successfully.',
                'order'        => $orderFresh,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
