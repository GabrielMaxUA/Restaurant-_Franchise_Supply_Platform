<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
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
        
        // Define excluded statuses
        $excludedStatuses = ['cancelled', 'rejected'];
        
        // Calculate weekly spending (This week)
        $weeklySpending = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->whereNotIn('status', $excludedStatuses)
            ->sum('total_amount');
        
        // Calculate monthly spending (Current month)
        $monthlySpending = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('status', $excludedStatuses)
            ->sum('total_amount');
            
        // Calculate last month's spending for comparison
        $lastMonthSpending = Order::where('user_id', $user->id)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->whereNotIn('status', $excludedStatuses)
            ->sum('total_amount');
            
        // Calculate spending change percentage
        $spendingChange = 0;
        if ($lastMonthSpending > 0) {
            $spendingChange = round((($monthlySpending - $lastMonthSpending) / $lastMonthSpending) * 100);
        }
        
        // Define active order statuses
        $activeStatuses = ['pending', 'processing', 'shipped', 'out_for_delivery'];
        
        // Calculate key stats
        $stats = [
            // Count pending orders
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', $activeStatuses)
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
                ->whereIn('status', $activeStatuses)
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
                ->whereNotIn('status', $excludedStatuses)
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
                ->whereNotIn('status', $excludedStatuses)
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
        
        // Get recent orders (include all statuses for display)
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
        $popular_products = Product::withCount(['orderItems' => function($query) use ($user, $excludedStatuses) {
                $query->whereHas('order', function($q) use ($user, $excludedStatuses) {
                    $q->where('user_id', $user->id)
                      ->whereNotIn('status', $excludedStatuses);
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
            
            // Check if product has in-stock variants
            $product->has_in_stock_variants = $product->variants()
                ->where('inventory_count', '>', 0)
                ->exists();
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
public function apiDashboard()
{
    $user = Auth::user();

    // Date calculations for reporting periods
    $now = Carbon::now();
    $startOfWeek = $now->copy()->startOfWeek();
    $endOfWeek = $now->copy()->endOfWeek();
    $startOfMonth = $now->copy()->startOfMonth();
    $endOfMonth = $now->copy()->endOfMonth();
    $lastMonth = $now->copy()->subMonth();
    $startOfLastMonth = $lastMonth->copy()->startOfMonth();
    $endOfLastMonth = $lastMonth->copy()->endOfMonth();

    // Define status groups
    $excludedStatuses = ['cancelled', 'rejected'];
    $activeStatuses = ['pending', 'processing', 'shipped', 'out_for_delivery'];

    // Calculate spending metrics
    $monthlySpending = Order::where('user_id', $user->id)
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->whereNotIn('status', $excludedStatuses)
        ->sum('total_amount');

    $lastMonthSpending = Order::where('user_id', $user->id)
        ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
        ->whereNotIn('status', $excludedStatuses)
        ->sum('total_amount');

    // Calculate spending change percentage
    $spendingChange = 0;
    if ($lastMonthSpending > 0) {
        $spendingChange = round((($monthlySpending - $lastMonthSpending) / $lastMonthSpending) * 100);
    }

    // Calculate pending orders metrics
    $pendingOrders = Order::where('user_id', $user->id)
        ->whereIn('status', $activeStatuses)
        ->count();
        
    $lastMonthPendingOrders = Order::where('user_id', $user->id)
        ->whereIn('status', $activeStatuses)
        ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
        ->count();
    
    // Calculate pending orders change percentage
    $pendingOrdersChange = 0;
    if ($lastMonthPendingOrders > 0) {
        $pendingOrdersChange = round((($pendingOrders - $lastMonthPendingOrders) / $lastMonthPendingOrders) * 100);
    }

    // Collect all stats in one array
    $stats = [
        'pending_orders' => $pendingOrders,
        'monthly_spending' => number_format($monthlySpending, 2, '.', ''),
        'spending_change' => $spendingChange,
        'low_stock_items' => 0,  // This appears to be a placeholder in the original code
        'incoming_deliveries' => Order::where('user_id', $user->id)
            ->whereIn('status', ['shipped', 'out_for_delivery'])
            ->count(),
        'pending_orders_change' => $pendingOrdersChange,
    ];

    // Generate weekly chart data with proper date ranges
    $weeklySpending = [];
    $weeklyOrders = [];
    
    for ($i = 0; $i < 7; $i++) {
        $day = $startOfWeek->copy()->addDays($i);
        $dayStart = $day->copy()->startOfDay();
        $dayEnd = $day->copy()->endOfDay();
        
        // Get orders for this day with proper filters
        $dayOrders = Order::where('user_id', $user->id)
            ->whereNotIn('status', $excludedStatuses)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get();
        
        // Calculate spending and count for this day
        $daySpending = $dayOrders->sum('total_amount');
        
        $weeklySpending[] = (float)$daySpending;
        $weeklyOrders[] = $dayOrders->count();
    }

    // Generate monthly chart data
    $monthlySpendingData = [];
    $monthlyOrdersData = [];
    $currentYear = Carbon::now()->startOfYear();
    
    for ($i = 0; $i < 12; $i++) {
        $month = $currentYear->copy()->addMonths($i);
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        
        // Get orders for this month
        $monthOrders = Order::where('user_id', $user->id)
            ->whereNotIn('status', $excludedStatuses)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->get();
        
        // Calculate spending and count for this month
        $monthSpending = $monthOrders->sum('total_amount');
        
        $monthlySpendingData[] = (float)$monthSpending;
        $monthlyOrdersData[] = $monthOrders->count();
    }

    // Get recent orders with properly formatted fields
    $recent_orders = Order::where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->take(5)
        ->get()
        ->map(function ($order) {
            // Count items for this order
            $itemsCount = OrderItem::where('order_id', $order->id)->sum('quantity');
            
            // Format the created_at date for display
            $createdAt = Carbon::parse($order->created_at)->format('Y-m-d');
            
            // Create an order_number field from invoice or ID
            $orderNumber = $order->invoice_number ?? 'ORD-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
            
            return [
                'id' => $order->id,
                'order_number' => $orderNumber,
                'status' => $order->status,
                'total' => (float)$order->total_amount,
                'total_amount' => (float)$order->total_amount, // Keep both for compatibility
                'shipping_address' => $order->shipping_address,
                'shipping_city' => $order->shipping_city,
                'shipping_state' => $order->shipping_state,
                'shipping_zip' => $order->shipping_zip,
                'delivery_date' => $order->delivery_date,
                'delivery_time' => $order->delivery_time,
                'delivery_preference' => $order->delivery_preference,
                'shipping_cost' => (float)$order->shipping_cost,
                'notes' => $order->notes,
                'manager_name' => $order->manager_name,
                'contact_phone' => $order->contact_phone,
                'purchase_order' => $order->purchase_order,
                'created_at' => $createdAt,
                'updated_at' => Carbon::parse($order->updated_at)->format('Y-m-d H:i:s'),
                'approved_at' => $order->approved_at,
                'invoice_number' => $order->invoice_number,
                'items_count' => (string)$itemsCount, // Cast to string for consistency with API
            ];
        });

    // Get popular products with properly formatted fields and images
    $popular_products = Product::withCount(['orderItems' => function($query) use ($user, $excludedStatuses) {
        $query->whereHas('order', function($q) use ($user, $excludedStatuses) {
            $q->where('user_id', $user->id)
              ->whereNotIn('status', $excludedStatuses);
        });
      }])
    ->orderBy('order_items_count', 'desc')
    ->take(6)
    ->get()
    ->map(function ($product) {
        // Handle image URL
        $imageUrl = null;
        if ($product->images && $product->images->isNotEmpty()) {
            $imageUrl = $product->images->first()->image_url;
            // Ensure image URL has full path if it's a relative URL
            if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                $imageUrl = url($imageUrl);
            }
        }
        
        return [
            'id' => $product->id,
            'name' => $product->name,
            'price' => number_format((float)($product->base_price ?? $product->price), 2, '.', ''),
            'unit_size' => $product->unit_size,
            'unit_type' => $product->unit_type,
            'image_url' => $imageUrl,
            'in_stock' => (bool)$product->variants()->where('inventory_count', '>', 0)->exists(),
            'inventory_count' => (int)$product->variants()->sum('inventory_count'), // Add inventory count
        ];
    });


try {
    // Get cart data for the current user
    $cart = Cart::where('user_id', $user->id)
        ->with(['items' => function($query) {
            $query->with('product'); // Include product details
        }])
        ->first();

    // Process cart data for the response
    $cartData = null;
    if ($cart) {
        // Count unique items (number of different products)
        $uniqueProductsCount = $cart->items->count();
        
        // Calculate total quantity (sum of all items)
        $totalQuantity = $cart->items->sum('quantity');
        
        // Calculate total price
        $cartTotal = $cart->items->sum(function($item) {
            return $item->quantity * $item->price;
        });

        // Format cart items
        $cartItems = $cart->items->map(function($item) {
            $product = $item->product;

            // Get image URL if available
            $imageUrl = null;
            if ($product && $product->images && $product->images->isNotEmpty()) {
                $imageUrl = $product->images->first()->image_url;
                if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
                    $imageUrl = url($imageUrl);
                }
            }

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $product ? $product->name : 'Unknown Product',
                'quantity' => (int)$item->quantity,
                'price' => (float)$item->price,
                'total' => (float)($item->quantity * $item->price),
                'image_url' => $imageUrl
            ];
        });

        $cartData = [
            'id' => $cart->id,
            'items_count' => $uniqueProductsCount,      // Count of unique products
            'total_quantity' => $totalQuantity,         // Sum of all quantities
            'unique_items_count' => $uniqueProductsCount, // Explicit field for unique count 
            'total' => (float)$cartTotal,
            'items' => $cartItems
        ];
    } else {
        // Empty cart
        $cartData = [
            'items_count' => 0,
            'total_quantity' => 0,
            'unique_items_count' => 0,
            'total' => 0,
            'items' => []
        ];
    }
} catch (\Exception $e) {
    // Log the error
    \Log::error('Error getting cart data: ' . $e->getMessage());
    \Log::error('Cart data error trace: ' . $e->getTraceAsString());
    
    // Provide default empty cart data
    $cartData = [
        'items_count' => 0,
        'total_quantity' => 0,
        'unique_items_count' => 0,
        'total' => 0,
        'items' => []
    ];
}
    // Build the full response with consistent data types
    return response()->json([
        'success' => true,
        'data' => [
            'stats' => $stats,
            'charts' => [
                'weekly_orders' => $weeklyOrders,
                'weekly_spending' => $weeklySpending,
                'monthly_orders' => $monthlyOrdersData,
                'monthly_spending' => $monthlySpendingData,
                // Add chart configuration values
                'step_sizes' => [
                    'orders' => 50,   // Step size for orders axis
                    'spending' => 10000  // Step size for spending axis
                ]
            ],
            'recent_orders' => $recent_orders,
            'popular_products' => $popular_products,
            'cart' => $cartData, 
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]
    ]);
}

}