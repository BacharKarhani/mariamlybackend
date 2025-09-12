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

    // Get orders grouped by month for authenticated user
    public function myOrdersByMonth(Request $request)
    {
        $user = $request->user();
        
        $query = Order::with('address', 'orderProducts.product')
            ->where('user_id', $user->id);

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->orderByDesc('date_added')->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found.'
            ]);
        }

        // Group orders by month
        $ordersByMonth = $orders->groupBy(function ($order) {
            return $order->date_added->format('Y-m'); // 2024-01, 2024-02, etc.
        });

        // Format the response
        $formattedOrders = [];
        foreach ($ordersByMonth as $month => $monthOrders) {
            $formattedOrders[] = [
                'month' => $month,
                'month_name' => $monthOrders->first()->date_added->format('F Y'), // January 2024
                'orders_count' => $monthOrders->count(),
                'total_amount' => $monthOrders->sum('total'),
                'orders' => $monthOrders->values()
            ];
        }

        return response()->json([
            'success' => true,
            'orders_by_month' => $formattedOrders
        ]);
    }

    // Get orders for a specific month
    public function getOrdersForMonth(Request $request, $year, $month)
    {
        $user = $request->user();
        
        // Validate month and year
        if ($month < 1 || $month > 12) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid month. Month must be between 1 and 12.'
            ], 400);
        }

        if ($year < 2020 || $year > date('Y') + 1) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid year.'
            ], 400);
        }

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $query = Order::with('address', 'orderProducts.product')
            ->where('user_id', $user->id)
            ->whereBetween('date_added', [$startDate, $endDate]);

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        $orders = $query->orderByDesc('date_added')->get();

        $monthName = $startDate->format('F Y');

        return response()->json([
            'success' => true,
            'month' => $monthName,
            'orders_count' => $orders->count(),
            'total_amount' => $orders->sum('total'),
            'orders' => $orders
        ]);
    }

    // Get order history summary for authenticated user
    public function myOrderHistory(Request $request)
    {
        $user = $request->user();
        
        $orders = Order::where('user_id', $user->id)->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No order history found.'
            ]);
        }

        // Calculate overall statistics
        $totalOrders = $orders->count();
        $totalSpent = $orders->sum('total');
        $averageOrderValue = $totalOrders > 0 ? $totalSpent / $totalOrders : 0;

        // Group by status
        $ordersByStatus = $orders->groupBy('order_status');
        $statusSummary = [];
        foreach ($ordersByStatus as $status => $statusOrders) {
            $statusSummary[] = [
                'status' => $status,
                'count' => $statusOrders->count(),
                'total_amount' => $statusOrders->sum('total')
            ];
        }

        // Get monthly summary for the last 12 months
        $monthlySummary = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            $monthOrders = $orders->whereBetween('date_added', [$monthStart, $monthEnd]);
            
            $monthlySummary[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('F Y'),
                'orders_count' => $monthOrders->count(),
                'total_amount' => $monthOrders->sum('total')
            ];
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total_orders' => $totalOrders,
                'total_spent' => $totalSpent,
                'average_order_value' => round($averageOrderValue, 2),
                'status_breakdown' => $statusSummary,
                'monthly_summary' => $monthlySummary
            ]
        ]);
    }

    // Admin: Get all orders grouped by month
    public function getAllOrdersByMonth(Request $request)
    {
        $query = Order::with('user', 'address');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('payment_code')) {
            $query->where('payment_code', $request->payment_code);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date_added', [$request->from, $request->to]);
        }

        $orders = $query->orderByDesc('date_added')->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found.'
            ]);
        }

        // Group orders by month
        $ordersByMonth = $orders->groupBy(function ($order) {
            return $order->date_added->format('Y-m');
        });

        // Format the response
        $formattedOrders = [];
        foreach ($ordersByMonth as $month => $monthOrders) {
            $formattedOrders[] = [
                'month' => $month,
                'month_name' => $monthOrders->first()->date_added->format('F Y'),
                'orders_count' => $monthOrders->count(),
                'total_revenue' => $monthOrders->sum('total'),
                'average_order_value' => round($monthOrders->sum('total') / $monthOrders->count(), 2),
                'orders' => $monthOrders->values()
            ];
        }

        return response()->json([
            'success' => true,
            'orders_by_month' => $formattedOrders
        ]);
    }

    // Admin: Get monthly statistics for dashboard
    public function getMonthlyStats(Request $request)
    {
        $months = $request->get('months', 12); // Default to last 12 months
        $months = min($months, 24); // Max 24 months for performance

        $monthlyStats = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            // Get orders for this month
            $monthOrders = Order::with('orderProducts.product')
                ->whereBetween('date_added', [$monthStart, $monthEnd])
                ->get();

            $ordersCount = $monthOrders->count();
            $totalRevenue = $monthOrders->sum('total');
            $totalProfit = 0;

            // Calculate profit for each order
            foreach ($monthOrders as $order) {
                foreach ($order->orderProducts as $orderProduct) {
                    $product = $orderProduct->product;
                    if ($product) {
                        $profitPerItem = $product->selling_price - $product->buying_price;
                        $totalProfit += $profitPerItem * $orderProduct->quantity;
                    }
                }
            }

            $monthlyStats[] = [
                'month' => $month->format('Y-m'),
                'month_name' => $month->format('F Y'),
                'month_short' => $month->format('M Y'),
                'orders_count' => $ordersCount,
                'total_revenue' => round($totalRevenue, 2),
                'total_profit' => round($totalProfit, 2),
                'profit_margin' => $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0,
                'average_order_value' => $ordersCount > 0 ? round($totalRevenue / $ordersCount, 2) : 0
            ];
        }

        // Calculate totals
        $totalOrders = collect($monthlyStats)->sum('orders_count');
        $totalRevenue = collect($monthlyStats)->sum('total_revenue');
        $totalProfit = collect($monthlyStats)->sum('total_profit');
        $overallProfitMargin = $totalRevenue > 0 ? round(($totalProfit / $totalRevenue) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'summary' => [
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'total_profit' => $totalProfit,
                'overall_profit_margin' => $overallProfitMargin,
                'average_order_value' => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0
            ],
            'monthly_data' => $monthlyStats
        ]);
    }

    // Admin: Get current month vs previous month comparison
    public function getMonthlyComparison(Request $request)
    {
        $currentMonth = now();
        $previousMonth = now()->subMonth();

        // Current month stats
        $currentMonthStart = $currentMonth->copy()->startOfMonth();
        $currentMonthEnd = $currentMonth->copy()->endOfMonth();
        
        $currentOrders = Order::with('orderProducts.product')
            ->whereBetween('date_added', [$currentMonthStart, $currentMonthEnd])
            ->get();

        $currentOrdersCount = $currentOrders->count();
        $currentRevenue = $currentOrders->sum('total');
        $currentProfit = 0;

        foreach ($currentOrders as $order) {
            foreach ($order->orderProducts as $orderProduct) {
                $product = $orderProduct->product;
                if ($product) {
                    $profitPerItem = $product->selling_price - $product->buying_price;
                    $currentProfit += $profitPerItem * $orderProduct->quantity;
                }
            }
        }

        // Previous month stats
        $previousMonthStart = $previousMonth->copy()->startOfMonth();
        $previousMonthEnd = $previousMonth->copy()->endOfMonth();
        
        $previousOrders = Order::with('orderProducts.product')
            ->whereBetween('date_added', [$previousMonthStart, $previousMonthEnd])
            ->get();

        $previousOrdersCount = $previousOrders->count();
        $previousRevenue = $previousOrders->sum('total');
        $previousProfit = 0;

        foreach ($previousOrders as $order) {
            foreach ($order->orderProducts as $orderProduct) {
                $product = $orderProduct->product;
                if ($product) {
                    $profitPerItem = $product->selling_price - $product->buying_price;
                    $previousProfit += $profitPerItem * $orderProduct->quantity;
                }
            }
        }

        // Calculate percentage changes
        $ordersChange = $previousOrdersCount > 0 ? 
            round((($currentOrdersCount - $previousOrdersCount) / $previousOrdersCount) * 100, 2) : 0;
        
        $revenueChange = $previousRevenue > 0 ? 
            round((($currentRevenue - $previousRevenue) / $previousRevenue) * 100, 2) : 0;
        
        $profitChange = $previousProfit > 0 ? 
            round((($currentProfit - $previousProfit) / $previousProfit) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'current_month' => [
                'month_name' => $currentMonth->format('F Y'),
                'orders_count' => $currentOrdersCount,
                'revenue' => round($currentRevenue, 2),
                'profit' => round($currentProfit, 2),
                'profit_margin' => $currentRevenue > 0 ? round(($currentProfit / $currentRevenue) * 100, 2) : 0
            ],
            'previous_month' => [
                'month_name' => $previousMonth->format('F Y'),
                'orders_count' => $previousOrdersCount,
                'revenue' => round($previousRevenue, 2),
                'profit' => round($previousProfit, 2),
                'profit_margin' => $previousRevenue > 0 ? round(($previousProfit / $previousRevenue) * 100, 2) : 0
            ],
            'changes' => [
                'orders_change_percent' => $ordersChange,
                'revenue_change_percent' => $revenueChange,
                'profit_change_percent' => $profitChange
            ]
        ]);
    }

}
