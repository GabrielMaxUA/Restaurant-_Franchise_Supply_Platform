<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.product', 'items.variant']);
        
        // If user is not admin, only show their own orders
        if ($request->user()->role->name !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json($orders);
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Calculate total amount and create order
        $totalAmount = 0;
        $orderItems = [];

        foreach ($validated['items'] as $item) {
            $product = Product::findOrFail($item['product_id']);
            $price = $product->base_price;
            
            // Add variant price adjustment if applicable
            if (isset($item['variant_id']) && $item['variant_id']) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $price += $variant->price_adjustment;
            }
            
            $itemTotal = $price * $item['quantity'];
            $totalAmount += $itemTotal;
            
            $orderItems[] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $price
            ];
        }

        // Create the order
        $order = Order::create([
            'user_id' => $request->user()->id,
            'status' => 'pending',
            'total_amount' => $totalAmount
        ]);

        // Add order items
        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
        }

        // Load relationships and return
        $order->load(['items.product', 'items.variant']);
        
        return response()->json($order, 201);
    }

    /**
     * Display the specified order.
     */
    public function show(Request $request, Order $order)
    {
        // Ensure user can only access their own orders unless admin
        if ($request->user()->role->name !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $order->load(['user', 'items.product', 'items.variant']);
        
        return response()->json($order);
    }

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,packed,shipped,delivered,cancelled'
        ]);

        $order->status = $validated['status'];
        $order->save();
        
        return response()->json($order);
    }

    /**
     * Sync order to QuickBooks.
     * This would be expanded in a real implementation.
     */
    public function syncToQuickbooks(Order $order)
    {
        // This is a placeholder for QuickBooks integration
        // In a real implementation, you would:
        // 1. Connect to QuickBooks API
        // 2. Create or update customer if needed
        // 3. Create invoice with line items
        // 4. Store the QuickBooks invoice ID
        
        $order->qb_invoice_id = 'QB-' . rand(10000, 99999); // Simulated ID
        $order->save();
        
        return response()->json([
            'message' => 'Order synced to QuickBooks',
            'qb_invoice_id' => $order->qb_invoice_id
        ]);
    }
}