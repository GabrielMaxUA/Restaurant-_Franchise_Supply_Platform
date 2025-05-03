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
        
        // Calculate key stats
        $stats = [
            // Count pending orders
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing', 'shipped', 'out_for_delivery'])
                ->count(),
                
            // Calculate monthly spending
            'monthly_spending' => Order::where('user_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->startOfMonth())
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
                
            // Placeholder for low stock items
            'low_stock_items' => 0,
                
            // Count incoming deliveries
            'incoming_deliveries' => Order::where('user_id', $user->id)
                ->whereIn('status', ['shipped', 'out_for_delivery'])
                ->count(),
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
            if ($product->images->isNotEmpty()) {
                $product->image_url = $product->images->first()->image_url;
            } else {
                $product->image_url = null;
            }
            
            // Normalize field names for the view
            $product->price = $product->base_price;
            $product->unit_size = ''; // Placeholder
            $product->unit_type = ''; // Placeholder
        }
        
        // Create empty sets for sections we're not implementing
        $inventory_alerts = [];
        $recent_activities = [];
        
        // Chart data (placeholders)
        $charts = [
            'weekly_orders' => [0, 0, 0, 0, 0, 0, 0],
            'weekly_spending' => [0, 0, 0, 0, 0, 0, 0],
            'monthly_orders' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            'monthly_spending' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
        ];
        
        return view('franchisee.dashboard', compact(
            'stats',
            'recent_orders',
            'popular_products',
            'inventory_alerts',
            'recent_activities',
            'charts'
        ));
    }
}