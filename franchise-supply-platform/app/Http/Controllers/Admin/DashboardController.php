<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Role;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Get counts for summary cards
        $pendingOrders = Order::where('status', 'pending')->count();
        $totalProducts = Product::count();
        
        // Get franchisee role ID
        $franchiseeRole = Role::where('name', 'franchisee')->first();
        $franchiseeUsers = 0;
        
        if ($franchiseeRole) {
            $franchiseeUsers = User::where('role_id', $franchiseeRole->id)->count();
        }
        
        // Calculate monthly revenue
        $startOfMonth = Carbon::now()->startOfMonth();
        $monthlyRevenue = Order::where('status', '!=', 'rejected')
            ->where('status', '!=', 'cancelled')
            ->where('created_at', '>=', $startOfMonth)
            ->sum('total_amount');
            
        // Get recent orders
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get low stock products
        $lowStockProducts = Product::with('category')
            ->where('inventory_count', '<=', 10)
            ->orderBy('inventory_count', 'asc')
            ->take(5)
            ->get();
            
        return view('admin.dashboard', compact(
            'pendingOrders',
            'totalProducts',
            'franchiseeUsers',
            'monthlyRevenue',
            'recentOrders',
            'lowStockProducts'
        ));
    }
}