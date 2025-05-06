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
     * Place a new order from cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function placeOrder(Request $request)
    {
        $user = Auth::user();
        
        // Validate request
        $request->validate([
            'shipping_address' => 'required|string',
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer',
            'notes' => 'nullable|string'
        ]);
        
        // Get cart items from session
        $cartItems = session('cart', []);
        
        if (empty($cartItems)) {
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add products before placing an order.');
        }
        
        DB::beginTransaction();
        
        try {
            // Create new order
            $order = new Order();
            $order->user_id = $user->id;
            $order->order_number = 'ORD-' . strtoupper(uniqid());
            $order->status = 'pending';
            $order->shipping_address = $request->shipping_address;
            $order->payment_method = $request->payment_method;
            $order->notes = $request->notes;
            $order->save();
            
            $totalAmount = 0;
            $inventoryErrors = [];
            
            // Process each cart item
            foreach ($cartItems as $key => $item) {
                // Parse the key to get product and variant IDs
                $ids = explode('_', $key);
                $productId = $ids[0];
                $variantId = isset($ids[1]) ? $ids[1] : null;
                
                // Get the product
                $product = Product::findOrFail($productId);
                
                // Set price and check inventory
                if ($variantId) {
                    $variant = ProductVariant::findOrFail($variantId);
                    $price = $variant->price;
                    
                    // Check inventory
                    if ($variant->inventory_count < $item['quantity']) {
                        $inventoryErrors[] = "Not enough inventory for {$product->name} ({$variant->name}). Available: {$variant->inventory_count}.";
                        continue;
                    }
                    
                    // Reduce inventory
                    $variant->inventory_count -= $item['quantity'];
                    $variant->save();
                } else {
                    $price = $product->price;
                    
                    // Check inventory
                    if ($product->inventory_count < $item['quantity']) {
                        $inventoryErrors[] = "Not enough inventory for {$product->name}. Available: {$product->inventory_count}.";
                        continue;
                    }
                    
                    // Reduce inventory
                    $product->inventory_count -= $item['quantity'];
                    $product->save();
                }
                
                // Create order item
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $productId;
                $orderItem->variant_id = $variantId;
                $orderItem->quantity = $item['quantity'];
                $orderItem->price = $price;
                $orderItem->save();
                
                // Add to total
                $totalAmount += ($price * $item['quantity']);
            }
            
            // If there were inventory errors, rollback and return with errors
            if (!empty($inventoryErrors)) {
                DB::rollBack();
                
                return redirect()->route('franchisee.cart')
                    ->with('error', 'Some items in your cart have inventory issues:<br>' . implode('<br>', $inventoryErrors));
            }
            
            // Update order total
            $order->total_amount = $totalAmount;
            $order->save();
            
            // Clear cart session
            session(['cart' => []]);
            
            DB::commit();
            
            return redirect()->route('franchisee.orders.details', $order->id)
                ->with('success', 'Order placed successfully! Your order number is ' . $order->order_number);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('franchisee.cart')
                ->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }
    
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
        
        // Calculate average order value - FIX for division by zero error
        $deliveredOrdersCount = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->count();
            
        if ($deliveredOrdersCount > 0) {
            $stats['avg_order_value'] = $stats['total_spent'] / $deliveredOrdersCount;
        } else {
            $stats['avg_order_value'] = 0;
        }
        
        return view('franchisee.order_history', compact('orders', 'stats'));
    }
    
    /**
     * Update order status and manage inventory accordingly
     *
     * @param  int  $id
     * @param  string  $status
     * @return \Illuminate\Http\Response
     */
    public function updateOrderStatus($id, $status)
    {
        $user = Auth::user();
        
        // Find the order and ensure it belongs to the user (or is admin)
        $order = Order::where('user_id', $user->id)
            ->findOrFail($id);
            
        $oldStatus = $order->status;
        
        // Check if status is valid
        if (!in_array($status, ['pending', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled'])) {
            return redirect()->back()->with('error', 'Invalid order status.');
        }
        
        // Handle inventory changes when cancelling an order
        if ($status == 'cancelled' && $oldStatus != 'cancelled') {
            // Restore inventory when cancelling
            $this->restoreInventory($order);
        }
        
        // Update order status
        $order->status = $status;
        
        // Set timestamp for specific statuses
        if ($status == 'delivered' && !$order->delivered_at) {
            $order->delivered_at = Carbon::now();
        } else if ($status == 'cancelled' && !$order->cancelled_at) {
            $order->cancelled_at = Carbon::now();
        }
        
        $order->save();
        
        return redirect()->back()->with('success', 'Order status updated successfully.');
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
            // Restore inventory for all items
            $this->restoreInventory($order);
            
            // Update order status
            $order->status = 'cancelled';
            $order->cancelled_at = Carbon::now(); // Add timestamp for cancellation
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
     * Restore inventory for all items in an order.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    private function restoreInventory($order)
    {
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
            $this->restoreInventory($order);
            
            // Update quantities and recalculate totals
            $totalAmount = 0;
            $invalidInventory = false;
            $inventoryErrors = [];
            
            foreach ($items as $item) {
                $newQuantity = isset($quantities[$item->id]) ? intval($quantities[$item->id]) : 0;
                
                if ($newQuantity <= 0) {
                    // Delete item if quantity is 0
                    $item->delete();
                    continue;
                }
                
                // Check inventory availability before updating
                $availableInventory = 0;
                
                if ($item->variant_id) {
                    $variant = ProductVariant::find($item->variant_id);
                    if ($variant) {
                        $availableInventory = $variant->inventory_count;
                        
                        // Check if we have enough inventory
                        if ($newQuantity > $availableInventory) {
                            $invalidInventory = true;
                            $inventoryErrors[] = "Only {$availableInventory} units of {$item->product->name} ({$variant->name}) are available.";
                            continue;
                        }
                        
                        // Update quantity
                        $item->quantity = $newQuantity;
                        $item->save();
                        
                        // Decrease inventory
                        $variant->inventory_count -= $newQuantity;
                        $variant->save();
                    }
                } else {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $availableInventory = $product->inventory_count;
                        
                        // Check if we have enough inventory
                        if ($newQuantity > $availableInventory) {
                            $invalidInventory = true;
                            $inventoryErrors[] = "Only {$availableInventory} units of {$item->product->name} are available.";
                            continue;
                        }
                        
                        // Update quantity
                        $item->quantity = $newQuantity;
                        $item->save();
                        
                        // Decrease inventory
                        $product->inventory_count -= $newQuantity;
                        $product->save();
                    }
                }
                
                $totalAmount += ($item->price * $newQuantity);
            }
            
            // If we have inventory issues, roll back and return with errors
            if ($invalidInventory) {
                DB::rollBack();
                
                return redirect()->route('franchisee.orders.modify', $order->id)
                    ->with('error', implode('<br>', $inventoryErrors))
                    ->withInput();
            }
            
            // Check if we still have items
            $remainingItems = OrderItem::where('order_id', $order->id)->count();
            
            if ($remainingItems == 0) {
                // No items left, cancel the order
                $order->status = 'cancelled';
                $order->cancelled_at = Carbon::now(); // Add timestamp for cancellation
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
        
        // Track inventory issues
        $inventoryIssues = [];
        
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
                $inventoryIssues[] = "Product '{$item->product_name}' is no longer available.";
                continue; // Skip if product doesn't exist anymore
            }
            
            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if (!$variant) {
                    $inventoryIssues[] = "Variant for '{$item->product_name}' is no longer available.";
                    continue; // Skip if variant doesn't exist anymore
                }
                
                // Check inventory
                if ($variant->inventory_count <= 0) {
                    $inventoryIssues[] = "'{$item->product_name} ({$variant->name})' is out of stock.";
                    continue; // Skip if out of stock
                }
                
                // Adjust quantity if needed
                $quantity = min($item->quantity, $variant->inventory_count);
                if ($quantity < $item->quantity) {
                    $inventoryIssues[] = "Only {$quantity} units of '{$item->product_name} ({$variant->name})' are available.";
                }
            } else {
                // Check inventory
                if ($product->inventory_count <= 0) {
                    $inventoryIssues[] = "'{$item->product_name}' is out of stock.";
                    continue; // Skip if out of stock
                }
                
                // Adjust quantity if needed
                $quantity = min($item->quantity, $product->inventory_count);
                if ($quantity < $item->quantity) {
                    $inventoryIssues[] = "Only {$quantity} units of '{$item->product_name}' are available.";
                }
            }
            
            // Add to cart
            if (isset($cart[$itemKey])) {
                $cart[$itemKey]['quantity'] += $quantity;
            } else {
                $cart[$itemKey] = [
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $quantity
                ];
            }
        }
        
        // Save updated cart to session
        session(['cart' => $cart]);
        
        if (count($inventoryIssues) > 0) {
            return redirect()->route('franchisee.cart')
                ->with('warning', 'Items from your previous order have been added to your cart with some modifications:<br>' . implode('<br>', $inventoryIssues));
        }
        
        return redirect()->route('franchisee.cart')
            ->with('success', 'Items from your previous order have been added to your cart.');
    }
    
    /**
     * Process an order and update inventory.
     * This would be called when an order is placed or updated from pending to processing.
     *
     * @param  App\Models\Order  $order
     * @return bool|array
     */
    private function processInventory($order)
    {
        $items = OrderItem::where('order_id', $order->id)->get();
        $inventoryErrors = [];
        
        foreach ($items as $item) {
            if ($item->variant_id) {
                $variant = ProductVariant::find($item->variant_id);
                if ($variant) {
                    if ($variant->inventory_count < $item->quantity) {
                        $inventoryErrors[] = "Not enough inventory for {$item->product->name} ({$variant->name}).";
                        continue;
                    }
                    
                    $variant->inventory_count -= $item->quantity;
                    $variant->save();
                }
            } else {
                $product = Product::find($item->product_id);
                if ($product) {
                    if ($product->inventory_count < $item->quantity) {
                        $inventoryErrors[] = "Not enough inventory for {$item->product->name}.";
                        continue;
                    }
                    
                    $product->inventory_count -= $item->quantity;
                    $product->save();
                }
            }
        }
        
        return empty($inventoryErrors) ? true : $inventoryErrors;
    }
}