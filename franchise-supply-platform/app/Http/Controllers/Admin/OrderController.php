<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function checkNewOrders()
    {
        $pendingOrdersCount = Order::where('status', 'pending')->count();
        $lowInventoryCount = Product::where('inventory_count', '<=', 10)->count();
        
        return response()->json([
            'pending_orders_count' => $pendingOrdersCount,
            'low_inventory_count' => $lowInventoryCount
        ]);
    }
    
    public function show(Order $order)
    {
        // Eager load the order items with products and variants
        $order->load(['items.product.images', 'items.variant', 'user']);
        
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,packed,shipped,delivered,cancelled'
        ]);
        
        $order->status = $request->status;
        $order->save();
        
        return redirect()->back()->with('success', "Order status updated to {$request->status}");
    }

    // This method will be implemented with the QuickBooks integration
    public function syncToQuickBooks(Order $order)
    {
        // Only sync approved orders that haven't been synced yet
        if ($order->status == 'approved' && !$order->qb_invoice_id) {
            $qbService = new \App\Services\QuickBooksService();
            $qbInvoiceId = $qbService->syncInvoice($order);
            
            return redirect()->route('admin.orders.show', $order)
                ->with('success', "Order synced to QuickBooks successfully. Invoice ID: $qbInvoiceId");
        }
        
        return redirect()->route('admin.orders.show', $order)
            ->with('error', 'Order could not be synced to QuickBooks. It must be approved and not already synced.');
    }
}