<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Count total products
        $totalProducts = Product::count();
        
        // Count in-stock products (inventory > 10)
        $inStockProducts = Product::where('inventory_count', '>', 10)->count();
        
        // Get low stock products
        $lowStockProducts = Product::with('category')
            ->where('inventory_count', '<=', 10)
            ->where('inventory_count', '>', 0)
            ->orderBy('inventory_count', 'asc')
            ->take(5)
            ->get();
            
        // Get out of stock products
        $outOfStockProducts = Product::with('category')
            ->where('inventory_count', 0)
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();
            
        // Get popular products based on order counts
        $popularProducts = Product::with('category')
            ->select('products.*', DB::raw('COUNT(order_items.id) as orders_count'))
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->groupBy(
                'products.id', 
                'products.name', 
                'products.description', 
                'products.base_price',  // Changed from price to base_price
                'products.inventory_count', 
                'products.category_id', 
                'products.created_at', 
                'products.updated_at'
                // Removed sku and active which don't exist
            )
            ->orderBy('orders_count', 'desc')
            ->take(5)
            ->get();
        
        return view('warehouse.dashboard', compact(
            'totalProducts',
            'inStockProducts',
            'lowStockProducts',
            'outOfStockProducts',
            'popularProducts'
        ));
    }
}