<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Count total products
        $totalProducts = Product::count();
        
        // Count in-stock products (inventory > 10, doesn't matter if variant is in stock)
        $inStockCount = Product::where('inventory_count', '>', 10)->count() +
                        ProductVariant::where('inventory_count', '>', 10)->count();
        
        // Count low-stock products (inventory between 1-10)
        $lowStockCount = Product::where('inventory_count', '>', 0)
                           ->where('inventory_count', '<=', 10)
                           ->count() +
                         ProductVariant::where('inventory_count', '>', 0)
                           ->where('inventory_count', '<=', 10)
                           ->count();
        
        // Count out-of-stock products and variants
        $outOfStockCount = Product::where('inventory_count', 0)->count() +
                          ProductVariant::where('inventory_count', 0)->count();
        
        // Get low stock products WITH their variants regardless of inventory
        $lowStockProducts = Product::with(['category', 'variants'])
            ->where(function($q) {
                // Get products with low stock OR products with low stock variants
                $q->where(function($mq) {
                    $mq->where('inventory_count', '>', 0)
                       ->where('inventory_count', '<=', 10);
                })
                ->orWhereHas('variants', function($vq) {
                    $vq->where('inventory_count', '>', 0)
                       ->where('inventory_count', '<=', 10);
                });
            })
            ->take(10)
            ->get();
        
        // Get out of stock products WITH their variants regardless of inventory
        $outOfStockProducts = Product::with(['category', 'variants'])
            ->where(function($q) {
                // Get products that are out of stock OR have out of stock variants
                $q->where('inventory_count', 0)
                  ->orWhereHas('variants', function($vq) {
                      $vq->where('inventory_count', 0);
                  });
            })
            ->take(10)
            ->get();
        
        // Get popular products based on order counts
        $popularProducts = Product::with(['category', 'variants'])
            ->select('products.*', DB::raw('COUNT(order_items.id) as orders_count'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy(
                'products.id', 
                'products.name', 
                'products.description', 
                'products.base_price',
                'products.inventory_count', 
                'products.category_id', 
                'products.created_at', 
                'products.updated_at'
            )
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get();
            
        // Get orders waiting for fulfillment (approved status)
        $pendingOrders = Order::where('status', 'approved')->count();
        $approvedOrders = $pendingOrders; // Set approved orders count for the alert
        
        // Get all orders EXCEPT delivered and rejected
        $orders = Order::whereNotIn('status', ['delivered', 'rejected'])->count();
        
        // Get recent orders - only orders that are not delivered or rejected
        $recentOrders = Order::with('user')
            ->whereNotIn('status', ['delivered', 'rejected'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
        
        return view('warehouse.dashboard', compact(
            'totalProducts',
            'inStockCount',
            'lowStockCount',
            'outOfStockCount',
            'lowStockProducts',
            'outOfStockProducts',
            'popularProducts',
            'pendingOrders',
            'approvedOrders',
            'orders',
            'recentOrders'
        ));
    }
}