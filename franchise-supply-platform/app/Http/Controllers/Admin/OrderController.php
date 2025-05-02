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

    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'items.variant']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,packed,shipped,delivered,cancelled'
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // Handle inventory updates when order is approved or rejected
        if ($oldStatus == 'pending' && $request->status == 'approved') {
            // Deduct inventory when order is approved
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product) {
                    // If the product has variant
                    if ($item->variant) {
                        $variant = $item->variant;
                        $variant->inventory_count = max(0, $variant->inventory_count - $item->quantity);
                        $variant->save();
                    } else {
                        // Deduct from main product inventory
                        $product->inventory_count = max(0, $product->inventory_count - $item->quantity);
                        $product->save();
                    }
                }
            }
        } else if ($oldStatus == 'approved' && in_array($request->status, ['rejected', 'cancelled'])) {
            // Return inventory when approved order is rejected or cancelled
            foreach ($order->items as $item) {
                $product = $item->product;
                
                if ($product) {
                    // If the product has variant
                    if ($item->variant) {
                        $variant = $item->variant;
                        $variant->inventory_count += $item->quantity;
                        $variant->save();
                    } else {
                        // Add back to main product inventory
                        $product->inventory_count += $item->quantity;
                        $product->save();
                    }
                }
            }
        }

        // Add QuickBooks integration code here (we'll implement this next)
        if ($oldStatus == 'pending' && $request->status == 'approved') {
            // Placeholder for QuickBooks sync
            // $this->syncToQuickBooks($order);
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', "Order status updated to " . ucfirst($request->status));
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