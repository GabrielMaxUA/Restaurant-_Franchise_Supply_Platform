<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the franchisee dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get the start and end dates for calculations
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $lastMonth = $now->copy()->subMonth();
        $startOfLastMonth = $lastMonth->copy()->startOfMonth();
        $endOfLastMonth = $lastMonth->copy()->endOfMonth();
        
        // Calculate weekly spending (This week)
        $weeklySpending = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
        
        // Calculate monthly spending (Current month)
        $monthlySpending = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
            
        // Calculate last month's spending for comparison
        $lastMonthSpending = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');
            
        // Calculate spending change percentage
        $spendingChange = 0;
        if ($lastMonthSpending > 0) {
            $spendingChange = round((($monthlySpending - $lastMonthSpending) / $lastMonthSpending) * 100);
        }
        
        // Calculate key stats
        $stats = [
            // Count pending orders
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing', 'shipped', 'out_for_delivery'])
                ->count(),
                
            // Calculate monthly spending
            'monthly_spending' => $monthlySpending,
            
            // Calculate spending change
            'spending_change' => $spendingChange,
                
            // Placeholder for low stock items
            'low_stock_items' => 0,
                
            // Count incoming deliveries
            'incoming_deliveries' => Order::where('user_id', $user->id)
                ->whereIn('status', ['shipped', 'out_for_delivery'])
                ->count(),
                
            // Count last month pending orders for comparison
            'last_month_pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing', 'shipped', 'out_for_delivery'])
                ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
                ->count(),
        ];
        
        // Calculate pending orders change
        $pendingOrdersChange = 0;
        if ($stats['last_month_pending_orders'] > 0) {
            $pendingOrdersChange = round((($stats['pending_orders'] - $stats['last_month_pending_orders']) / $stats['last_month_pending_orders']) * 100);
        }
        $stats['pending_orders_change'] = $pendingOrdersChange;
        
        // Generate chart data for weekly spending and orders
        $weeklySpendingData = [];
        $weeklyOrdersData = [];
        
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();
            
            // Get orders for this day
            $dayOrders = Order::where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->get();
            
            // Calculate total spending for this day
            $daySpending = $dayOrders->sum('total_amount');
            
            // Add to data arrays
            $weeklySpendingData[] = $daySpending;
            $weeklyOrdersData[] = $dayOrders->count();
        }
        
        // Generate chart data for monthly spending and orders
        $monthlySpendingData = [];
        $monthlyOrdersData = [];
        
        $currentYear = Carbon::now()->startOfYear();
        
        for ($i = 0; $i < 12; $i++) {
            $month = $currentYear->copy()->addMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            // Get orders for this month
            $monthOrders = Order::where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get();
            
            // Calculate total spending for this month
            $monthSpending = $monthOrders->sum('total_amount');
            
            // Add to data arrays
            $monthlySpendingData[] = $monthSpending;
            $monthlyOrdersData[] = $monthOrders->count();
        }
        
        // Create charts array with actual data
        $charts = [
            'weekly_orders' => $weeklyOrdersData,
            'weekly_spending' => $weeklySpendingData,
            'monthly_orders' => $monthlyOrdersData,
            'monthly_spending' => $monthlySpendingData,
        ];
        
        // Add chart configuration values
        $charts['step_sizes'] = [
            'orders' => 50,   // Step size for orders axis
            'spending' => 10000  // Step size for spending axis
        ];
        
        // Get recent orders
        $recent_orders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Add items_count to each order
        foreach ($recent_orders as $order) {
            $order->items_count = OrderItem::where('order_id', $order->id)->sum('quantity');
            $order->total = $order->total_amount; // Normalize field name for the view
        }
        
        // Get popular products (based on order frequency)
        $popular_products = Product::withCount(['orderItems' => function($query) use ($user) {
                $query->whereHas('order', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            }])
            ->orderBy('order_items_count', 'desc')
            ->take(6)
            ->get();
            
        // Add image and format price for each product
        foreach ($popular_products as $product) {
            // Get first image if available
            if ($product->images && $product->images->isNotEmpty()) {
                $product->image_url = $product->images->first()->image_url;
            } else {
                $product->image_url = null;
            }
            
            // Normalize field names for the view
            $product->price = $product->base_price ?? $product->price;
            $product->unit_size = $product->unit_size ?? '';
            $product->unit_type = $product->unit_type ?? '';
        }
        
        // Create empty sets for sections we're not implementing
        $recent_activities = [];
        $products = $popular_products;
        return view('franchisee.dashboard', compact(
            'stats',
            'weeklySpending',
            'recent_orders',
            'popular_products',
            'recent_activities',
            'charts',
            'products'
        ));
    }
}