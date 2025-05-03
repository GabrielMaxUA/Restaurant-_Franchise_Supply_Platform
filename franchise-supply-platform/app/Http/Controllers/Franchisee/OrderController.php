<?php

namespace App\Http\Controllers\Franchisee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    /**
     * Display pending orders.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingOrders()
    {
        $user = Auth::user();
        
        // Get orders that are still in progress
        $orders = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'shipped', 'out_for_delivery'])
            ->with(['items.product', 'items.variant'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        // Calculate counts for summary stats
        $order_counts = [
            'total' => $orders->total(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'shipped' => Order::where('user_id', $user->id)->where('status', 'shipped')->count(),
            'out_for_delivery' => Order::where('user_id', $user->id)->where('status', 'out_for_delivery')->count(),
        ];
        
        return view('franchisee.pending_orders', compact('orders', 'order_counts'));
    }
    
    /**
     * Display order history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function orderHistory(Request $request)
    {
        $user = Auth::user();
        
        // Build query
        $query = Order::where('user_id', $user->id)
            ->whereIn('status', ['delivered', 'cancelled']);
            
        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Apply sorting
        if ($request->filled('sort_by')) {
            switch ($request->sort_by) {
                case 'date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'total_asc':
                    $query->orderBy('total_amount', 'asc');
                    break;
                case 'total_desc':
                    $query->orderBy('total_amount', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            // Default sorting
            $query->orderBy('created_at', 'desc');
        }
        
        // Eager load relationships
        $query->with(['items.product']);
        
        // Get orders
        $orders = $query->paginate(15);
        
        // Calculate stats
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)
                ->whereIn('status', ['delivered', 'cancelled'])
                ->count(),
                
            'total_spent' => Order::where('user_id', $user->id)
                ->where('status', 'delivered')
                ->sum('total_amount'),
                
            'total_items' => OrderItem::whereHas('order', function($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->where('status', 'delivered');
                })
                ->sum('quantity'),
        ];
        
        // Calculate average order value
        if ($stats['total_orders'] > 0) {
            $stats['avg_order_value'] = $stats['total_spent'] / Order::where('user_id', $user->id)
                ->where('status', 'delivered')
                ->count();
        } else {
            $stats['avg_order_value'] = 0;
        }
        
        return view('franchisee.order_history', compact('orders', 'stats'));
    }
    
    /**
     * Display order details.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function orderDetails($id)
    {
        $user = Auth::user();
        
        $order = Order::where('user_id', $user->id)
            ->with(['items.product', 'items.variant'])
            ->findOrFail($id);
            
        // Count total items
        $order->items_count = $order->items->sum('quantity');
        
        // Format created_at as estimated delivery date (just for display purposes)
        if (!$order->estimated_delivery && in_array($order->status, ['pending', 'processing', 'shipped', 'out_for_delivery'])) {
            // Just an example, would typically be set elsewhere
            $order->estimated_delivery = $order->created_at->addDays(7);
        }
        
        return view('franchisee.order_details', compact('order'));
    }
    
    /**
     * Cancel an order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelOrder($id)
    {
        $user = Auth::user();
        
        // Find the order and ensure it belongs to the user
        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);
            
        // Transaction for consistency
        DB::beginTransaction();
        
        try {
            // Get order items
            $items = OrderItem::where('order_id', $order->id)->get();
            
            // Restore inventory for each item
            foreach ($items as $item) {
                if ($item->variant_id) {
                    // Restore variant inventory
                    $variant = ProductVariant::find($item->variant_id);
                    if ($variant) {
                        $variant->inventory_count += $item->quantity;
                        $variant->save();
                    }
                } else {
                    // Restore product inventory
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->inventory_count += $item->quantity;
                        $product->save();
                    }
                }
            }
            
            // Update order status
            $order->status = 'cancelled';
            $order->save();
            
            DB::commit();
            
            return redirect()->route('franchisee.orders.pending')
                ->with('success', 'Order has been cancelled successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('franchisee.orders.pending')
                ->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
    
    /**
     * View form to modify an order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function modifyOrder($id)
    {
        $user = Auth::user();
        
        // Find the order and ensure it belongs to the user
        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['items.product', 'items.variant'])
            ->findOrFail($id);
            
        return view('franchisee.modify_order', compact('order'));
    }
    
    /**
     * Update an order.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request, $id)
    {
        $user = Auth::user();
        
        // Find the order and ensure it belongs to the user
        $order = Order::where('user_id', $user->id)
            ->where('status', 'pending')
            ->findOrFail($id);
            
        $request->validate([
            'quantity' => 'required|array',
            'quantity.*' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update notes
            $order->notes = $request->notes;
            
            // Get quantities from request
            $quantities = $request->input('quantity', []);
            
            // Get current items
            $items = OrderItem::where('order_id', $order->id)->get();
            
            // First restore all inventory
            foreach ($items as $item) {
                if ($item->variant_id) {
                    $variant = ProductVariant::find($item->variant_id);
                    if ($variant) {
                        $variant->inventory_count += $item->quantity;
                        $variant->save();
                    }
                } else {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->inventory_count += $item->quantity;
                        $product->save();
                    }
                }
            }
            
            // Update quantities and recalculate totals
            $totalAmount = 0;
            
            foreach ($items as $item) {
                $newQuantity = isset($quantities[$item->id]) ? intval($quantities[$item->id]) : 0;
                
                if ($newQuantity <= 0) {
                    // Delete item if quantity is 0
                    $item->delete();
                    continue;
                }
                
                // Update quantity
                $item->quantity = $newQuantity;
                $item->save();
                
                // Reduce inventory again with new quantity
                if ($item->variant_id) {
                    $variant = ProductVariant::find($item->variant_id);
                    if ($variant) {
                        $variant->inventory_count = max(0, $variant->inventory_count - $newQuantity);
                        $variant->save();
                    }
                } else {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->inventory_count = max(0, $product->inventory_count - $newQuantity);
                        $product->save();
                    }
                }
                
                $totalAmount += ($item->price * $newQuantity);
            }
            
            // Check if we still have items
            $remainingItems = OrderItem::where('order_id', $order->id)->count();
            
            if ($remainingItems == 0) {
                // No items left, cancel the order
                $order->status = 'cancelled';
                $order->save();
                
                DB::commit();
                
                return redirect()->route('franchisee.orders.pending')
                    ->with('info', 'Order has been cancelled as all items were removed.');
            }
            
            // Update order total
            $order->total_amount = $totalAmount;
            $order->save();
            
            DB::commit();
            
            return redirect()->route('franchisee.orders.details', $order->id)
                ->with('success', 'Order updated successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('franchisee.orders.modify', $order->id)
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }
    
    /**
     * Repeat a previous order.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function repeatOrder($id)
    {
        $user = Auth::user();
        
        // Find the order and ensure it belongs to the user
        $order = Order::where('user_id', $user->id)
            ->with('items')
            ->findOrFail($id);
            
        // Get current cart
        $cart = session('cart', []);
        
        // Add items from the order to the cart
        foreach ($order->items as $item) {
            // Create a unique key for this product/variant combination
            $itemKey = $item->product_id;
            if ($item->variant_id) {
                $itemKey .= '_' . $item->variant_id;
            }
            
            // Check if product and variant still exist and have inventory
            $product = Product::find($item->product_id);
            
            if (!$product) {
                continue; // Skip if product doesn't exist anymore
            }
            
            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if (!$variant) {
                    continue; // Skip if variant doesn't exist anymore
                }
                
                // Check inventory
                if ($variant->inventory_count <= 0) {
                    continue; // Skip if out of stock
                }
                
                // Adjust quantity if needed
                $quantity = min($item->quantity, $variant->inventory_count);
            } else {
                // Check inventory
                if ($product->inventory_count <= 0) {
                    continue; // Skip if out of stock
                }
                
                // Adjust quantity if needed
                $quantity = min($item->quantity, $product->inventory_count);
            }
            
            // Add to cart
            if (isset($cart[$itemKey])) {
                $cart[$itemKey]['quantity'] += $quantity;
            } else {
                $cart[$itemKey] = [
                    'quantity' => $quantity
                ];
            }
        }
        
        // Save updated cart to session
        session(['cart' => $cart]);
        
        return redirect()->route('franchisee.cart')
            ->with('success', 'Items from your previous order have been added to your cart.');
    }
}