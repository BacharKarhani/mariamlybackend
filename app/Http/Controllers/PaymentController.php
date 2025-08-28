<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function whishCallbackSuccess($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->order_status = 'completed';
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment successful. Order completed.',
            'order_id' => $order_id,
        ]);
    }

    public function whishCallbackFailure($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->order_status = 'failed';
        $order->save();

        return response()->json([
            'success' => false,
            'message' => 'Payment failed. Order canceled.',
            'order_id' => $order_id,
        ]);
    }

    public function whishRedirectSuccess($order_id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Redirected after successful payment.',
            'order_id' => $order_id,
        ]);
    }

    public function whishRedirectFailure($order_id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Redirected after failed payment.',
            'order_id' => $order_id,
        ]);
    }
}
