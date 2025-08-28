<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Get all orders paginated with optional filters
    public function indexPaginated(Request $request)
    {
        $query = Order::with('user', 'address');

        // Filter by first and last name
        if ($request->filled('fname') && $request->filled('lname')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('fname', 'like', '%' . $request->fname . '%')
                    ->where('lname', 'like', '%' . $request->lname . '%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_code')) {
            $query->where('payment_code', $request->payment_code);
        }

        // Filter by date range
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date_added', [$request->from, $request->to]);
        }

        $orders = $query->orderByDesc('date_added')->paginate(10);

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    // Get single order by ID
    public function show($order_id)
    {
        $order = Order::with('user', 'address')->find($order_id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'order' => $order
        ]);
    }


    // Admin: Update order status (pending, processing, delivered)
    public function updateStatus(Request $request, $order_id)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,delivered',
        ]);

        $order = Order::find($order_id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->update([
            'order_status' => $request->order_status,
            'date_modified' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }


    // Admin: Get order profit calculation
    public function getOrderProfit($order_id)
    {
        $order = Order::with('orderProducts.product')->find($order_id);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $totalProfit = 0;

        foreach ($order->orderProducts as $orderProduct) {
            $product = $orderProduct->product;

            if ($product) {
                $profitPerItem = $product->selling_price - $product->buying_price;
                $totalProfit += $profitPerItem * $orderProduct->quantity;
            }
        }

        return response()->json([
            'success' => true,
            'order_id' => $order->order_id,
            'total_profit' => number_format($totalProfit, 2)
        ]);
    }

    // Get orders for the authenticated user with optional status filter
public function myOrders(Request $request)
{
    $user = $request->user();

    $query = Order::with('address', 'orderProducts.product')
        ->where('user_id', $user->id);

    // Optional status filter
    if ($request->filled('status')) {
        $query->where('order_status', $request->status);
    }

    $orders = $query->orderByDesc('date_added')->paginate(10);

    if ($orders->isEmpty()) {
        return response()->json([
            'success' => false,
            'message' => 'No orders found.'
        ]);
    }

    return response()->json([
        'success' => true,
        'orders' => $orders
    ]);
}

}
