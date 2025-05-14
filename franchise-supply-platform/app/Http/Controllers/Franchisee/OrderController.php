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
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    /**
     * Constructor - Check for order updates on initialization
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check if user is authenticated before trying to get order updates
            if (Auth::check()) {
                // Check directly in the constructor for order updates
                $user = Auth::user();
                $hasUpdates = Order::where('user_id', $user->id)
                    ->whereIn('status', ['pending', 'processing', 'packed', 'shipped', 'delayed', 'rejected', 'cancelled'])
                    ->where('updated_at', '>=', Carbon::now()->subDays(7))
                    ->exists();
                    
                if ($hasUpdates) {
                    session(['has_order_updates' => true]);
                    session(['welcome_back' => true]);
                    session(['hide_welcome' => false]); 
                }
            }
            return $next($request);
        });
    }
    
    /**
     * Get recent order status updates for the current user and store in session
     * 
     * @return void
     */
    private function setOrderStatusUpdates()
    {
        $user = Auth::user();
        if (!$user) return;
        
        // Get orders that have been updated in the last 7 days
        $recentlyUpdatedOrders = Order::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'packed', 'shipped', 'delayed', 'rejected', 'cancelled'])
            ->where('updated_at', '>=', Carbon::now()->subDays(7))
            ->count();
        
        // If there are any updated orders, set the flag
        if ($recentlyUpdatedOrders > 0) {
            // Store simple flag that there are updates
            session(['has_order_updates' => true]);
            session(['welcome_back' => true]);
            session(['hide_welcome' => false]);
        }
    }

    /**
     * Place a new order from cart.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function placeOrder(Request $request)
    {
        $user = Auth::user();
        
        // Validate request with all the fields from the checkout
        $request->validate([
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string',
            'shipping_state' => 'required|string',
            'shipping_zip' => 'required|string',
            'delivery_preference' => 'required|string|in:standard,express,scheduled',
            'delivery_date' => 'required|date',
            'delivery_time' => 'required|string|in:morning,afternoon,evening',
            'notes' => 'nullable|string',
            'manager_name' => 'nullable|string',
            'contact_phone' => 'nullable|string'
        ]);
        
        // Get cart items from session
        $cartItems = session('cart', []);
        
        if (empty($cartItems)) {
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add products before placing an order.');
        }
        
        DB::beginTransaction();
        
        try {
            // Combine all address information into a single string
            $fullAddress = $request->shipping_address . ', ' . 
                          $request->shipping_city . ', ' . 
                          $request->shipping_state . ' ' . 
                          $request->shipping_zip;
            
            // Store all the extra information in the notes field
            $notesText = '';
            if ($request->notes) {
                $notesText .= "Notes: " . $request->notes . "\n\n";
            }
            
            $notesText .= "Delivery Information:\n";
            $notesText .= "Date: " . $request->delivery_date . "\n";
            $notesText .= "Time: " . $request->delivery_time . "\n";
            $notesText .= "Preference: " . $request->delivery_preference . "\n";
            
            if ($request->manager_name) {
                $notesText .= "\nManager: " . $request->manager_name . "\n";
            }
            
            if ($request->contact_phone) {
                $notesText .= "Contact: " . $request->contact_phone . "\n";
            }
            
            // Calculate shipping cost
            $shippingCost = 0;
            if ($request->delivery_preference === 'express') {
                $shippingCost = 15.00;
            }
            
            // Create a new order record directly with DB to avoid Eloquent's mass assignment
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $user->id,
                'status' => 'pending',
                'shipping_address' => $fullAddress,
                'total_amount' => 0, // Will update this later
                'created_at' => now(),
                'updated_at' => now(),
                'delivery_date' => $request->delivery_date
            ]);
            
            // Now get the order so we can work with it
            $order = Order::find($orderId);
            
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
            
            // Add shipping cost to total amount if express delivery
            if ($request->delivery_preference === 'express') {
                $totalAmount += $shippingCost;
            }
            
            // Update the order with the final details
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'total_amount' => $totalAmount,
                    'notes' => $notesText
                ]);
            
            // Clear cart session
            session(['cart' => []]);
            
            DB::commit();
            
            // Flag that there are order updates
            session(['has_order_updates' => true]);
            
            return redirect()->route('franchisee.orders.details', $order->id)
                ->with('success', 'Order placed successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('franchisee.cart')
                ->with('error', 'Failed to place order: ' . $e->getMessage());
        }
    }
    
    /**
     * Display pending orders with optional status filtering.
     * Supports both web and API requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function pendingOrders(Request $request)
    {
        $user = Auth::user();
        
        // Clear order updates notification when viewing orders (only for web requests)
        if (!$request->expectsJson() && !$request->wantsJson()) {
            session(['has_order_updates' => false]);
        }
        
        // Build the base query
        $query = Order::where('user_id', $user->id);
        
        // Apply status filter if provided, otherwise use default statuses for pending orders
        if ($request->has('status')) {
            // Filter by specific status
            $status = $request->status;
            $query->where('status', $status);
        } else {
            // Default - show only orders that are in progress
            $query->whereIn('status', ['pending', 'processing', 'packed', 'shipped']);
        }
        
        // Get orders with pagination
        $orders = $query->with(['items.product', 'items.variant'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
                
        // Calculate counts for summary stats
        $order_counts = [
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'packed' => Order::where('user_id', $user->id)->where('status', 'packed')->count(),
            'shipped' => Order::where('user_id', $user->id)->where('status', 'shipped')->count(),
            'delivered' => Order::where('user_id', $user->id)->where('status', 'delivered')->count(),
            'rejected' => Order::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];
        
        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'orders' => $orders->items(),
                'order_counts' => $order_counts,
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total()
                ]
            ]);
        }
        
        // Web response
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
    
        // Clear order updates notification
        session(['has_order_updates' => false]);
    
        // Base query - now includes rejected orders along with pending and delivered
        $query = Order::where('user_id', $user->id)
                      ->whereIn('status', [ 'delivered', 'rejected']);
    
        // Apply optional filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
    
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    
        // Optional: filter within allowed statuses - now includes rejected
        if ($request->filled('status') && in_array($request->status, [ 'delivered', 'rejected'])) {
            $query->where('status', $request->status);
        }
    
        // Sorting logic
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
            $query->orderBy('created_at', 'desc');
        }
    
        // Eager load items
        $query->with(['items.product.images', 'items.variant']);
    
        // Debug log
        logger()->info('Order filters:', [
            'user_id' => $user->id,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'status' => $request->status,
            'sort_by' => $request->sort_by,
            'matching_rows' => $query->count()
        ]);
    
        // Paginated results
        $orders = $query->paginate(15);
    
        // Stats based only on delivered orders
        $deliveredQuery = Order::where('user_id', $user->id)->where('status', 'delivered');
    
        $stats = [
            'total_orders' => $query->count(),
            'total_spent' => $deliveredQuery->sum('total_amount'),
            'total_items' => OrderItem::whereHas('order', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('status', 'delivered');
            })->sum('quantity'),
            'avg_order_value' => 0
        ];
    
        $deliveredOrdersCount = $deliveredQuery->count();
        if ($deliveredOrdersCount > 0) {
            $stats['avg_order_value'] = $stats['total_spent'] / $deliveredOrdersCount;
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
        if (!in_array($status, ['pending', 'processing', 'packed', 'shipped', 'delivered', 'cancelled', 'rejected'])) {
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
        
        // Flag that there are order updates
        session(['has_order_updates' => true]);
        
        return redirect()->back()->with('success', 'Order status updated successfully.');
    }
    
    /**
     * Display order details.
     * Supports both web and API requests.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function orderDetails(Request $request, $id)
    {
        $user = Auth::user();
        
        // Clear order updates notification when viewing order details (web only)
        if (!$request->expectsJson() && !$request->wantsJson()) {
            session(['has_order_updates' => false]);
        }
        
        $order = Order::where('user_id', $user->id)
            ->with(['items.product', 'items.variant'])
            ->findOrFail($id);
            
        // Count total items
        $order->items_count = $order->items->sum('quantity');
        
        // Format created_at as estimated delivery date (just for display purposes)
        if (!$order->estimated_delivery && in_array($order->status, ['pending', 'processing', 'packed', 'shipped'])) {
            // Just an example, would typically be set elsewhere
            $order->estimated_delivery = $order->created_at->addDays(7);
        }
        
        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            // For API responses, format the order items to include only necessary data
            $items = [];
            foreach ($order->items as $item) {
                $itemData = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->price * $item->quantity
                ];
                
                // Add product details
                if ($item->product) {
                    $itemData['product'] = [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'description' => $item->product->description,
                        'image_url' => $item->product->images->isNotEmpty() ? $item->product->images->first()->image_url : null
                    ];
                }
                
                // Add variant details if applicable
                if ($item->variant) {
                    $itemData['variant'] = [
                        'id' => $item->variant->id,
                        'name' => $item->variant->name,
                        'price_adjustment' => $item->variant->price_adjustment
                    ];
                }
                
                $items[] = $itemData;
            }
            
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'shipping_address' => $order->shipping_address,
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                    'delivery_date' => $order->delivery_date,
                    'delivery_time' => $order->delivery_time,
                    'delivery_preference' => $order->delivery_preference,
                    'estimated_delivery' => $order->estimated_delivery,
                    'items_count' => $order->items_count,
                    'items' => $items
                ]
            ]);
        }
        
        // Web response
        return view('franchisee.order_details', compact('order'));
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
            ->with(['items.product', 'items.variant'])
            ->findOrFail($id);
        
        // Get or create the user's cart 
        $cart = new \App\Models\Cart();
        if (\App\Models\Cart::where('user_id', $user->id)->exists()) {
            $cart = \App\Models\Cart::where('user_id', $user->id)->first();
        } else {
            $cart->user_id = $user->id;
            $cart->save();
        }
        
        // Track inventory issues
        $inventoryIssues = [];
        $itemsAdded = false;
        $inventoryService = new \App\Services\InventoryService();
        
        // Begin transaction for database operations
        DB::beginTransaction();
        
        try {
            // Add items from the order to the cart
            foreach ($order->items as $item) {
                // Get the product with eager loaded relationships
                $product = $item->product;
                
                if (!$product) {
                    $inventoryIssues[] = "Product ID {$item->product_id} is no longer available.";
                    continue; // Skip if product doesn't exist anymore
                }
                
                $productName = $product->name ?? "Product #{$item->product_id}";
                $quantityToAdd = $item->quantity;
                
                if ($item->variant_id) {
                    // Handle variant product
                    $variant = $item->variant;
                    if (!$variant) {
                        $inventoryIssues[] = "Variant for '{$productName}' is no longer available.";
                        continue; // Skip if variant doesn't exist anymore
                    }
                    
                    $variantName = $variant->name ?? "Variant #{$item->variant_id}";
                    
                    // Check inventory availability
                    if ($variant->inventory_count <= 0) {
                        $inventoryIssues[] = "'{$productName} ({$variantName})' is out of stock.";
                        continue; // Skip if out of stock
                    }
                    
                    // Adjust quantity to match available inventory
                    if ($variant->inventory_count < $quantityToAdd) {
                        $inventoryIssues[] = "Only {$variant->inventory_count} units of '{$productName} ({$variantName})' are available (requested {$quantityToAdd}).";
                        $quantityToAdd = $variant->inventory_count;
                    }
                    
                    // Check if this variant is already in the cart
                    $existingCartItem = \App\Models\CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $item->product_id)
                        ->where('variant_id', $item->variant_id)
                        ->first();
                        
                    if ($existingCartItem) {
                        // Check if adding to existing cart item would exceed inventory
                        $newQuantity = $existingCartItem->quantity + $quantityToAdd;
                        if ($newQuantity > $variant->inventory_count) {
                            $inventoryIssues[] = "Adding {$quantityToAdd} more units of '{$productName} ({$variantName})' would exceed available inventory.";
                            $quantityToAdd = max(0, $variant->inventory_count - $existingCartItem->quantity);
                            $newQuantity = $existingCartItem->quantity + $quantityToAdd;
                        }
                        
                        if ($quantityToAdd > 0) {
                            $existingCartItem->quantity = $newQuantity;
                            $existingCartItem->save();
                            $itemsAdded = true;
                        }
                    } else if ($quantityToAdd > 0) {
                        // Add as new cart item
                        \App\Models\CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $item->product_id,
                            'variant_id' => $item->variant_id,
                            'quantity' => $quantityToAdd
                        ]);
                        $itemsAdded = true;
                    }
                } else {
                    // Handle main product (no variant)
                    if ($product->inventory_count <= 0) {
                        $inventoryIssues[] = "'{$productName}' is out of stock.";
                        continue; // Skip if out of stock
                    }
                    
                    // Adjust quantity to match available inventory
                    if ($product->inventory_count < $quantityToAdd) {
                        $inventoryIssues[] = "Only {$product->inventory_count} units of '{$productName}' are available (requested {$quantityToAdd}).";
                        $quantityToAdd = $product->inventory_count;
                    }
                    
                    // Check if this product is already in the cart
                    $existingCartItem = \App\Models\CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $item->product_id)
                        ->whereNull('variant_id')
                        ->first();
                        
                    if ($existingCartItem) {
                        // Check if adding to existing cart item would exceed inventory
                        $newQuantity = $existingCartItem->quantity + $quantityToAdd;
                        if ($newQuantity > $product->inventory_count) {
                            $inventoryIssues[] = "Adding {$quantityToAdd} more units of '{$productName}' would exceed available inventory.";
                            $quantityToAdd = max(0, $product->inventory_count - $existingCartItem->quantity);
                            $newQuantity = $existingCartItem->quantity + $quantityToAdd;
                        }
                        
                        if ($quantityToAdd > 0) {
                            $existingCartItem->quantity = $newQuantity;
                            $existingCartItem->save();
                            $itemsAdded = true;
                        }
                    } else if ($quantityToAdd > 0) {
                        // Add as new cart item
                        \App\Models\CartItem::create([
                            'cart_id' => $cart->id,
                            'product_id' => $item->product_id,
                            'variant_id' => null,
                            'quantity' => $quantityToAdd
                        ]);
                        $itemsAdded = true;
                    }
                }
            }
            
            // If no items could be added, rollback and return with error
            if (!$itemsAdded) {
                DB::rollBack();
                return redirect()->route('franchisee.cart')
                    ->with('error', 'Unable to add items from your previous order. All items are unavailable.');
            }
            
            // Commit the transaction
            DB::commit();
            
            // Return with appropriate message
            if (count($inventoryIssues) > 0) {
                return redirect()->route('franchisee.cart')
                    ->with('warning', 'Items from your previous order have been added to your cart with some modifications: ' . implode('\n', $inventoryIssues));
            }
            
            return redirect()->route('franchisee.cart')
                ->with('success', 'Items from your previous order have been added to your cart.');
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            \Log::error('Error repeating order: ' . $e->getMessage(), [
                'order_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('franchisee.cart')
                ->with('error', 'An error occurred while adding items to your cart: ' . $e->getMessage());
        }
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
    
    /**
     * Clear the welcome banner from the session.
     *
     * @return \Illuminate\Http\Response
     */
    public function dismissWelcomeBanner()
    {
        session(['hide_welcome' => true]);

        return redirect()->back();
    }

    /**
     * Generate and display an HTML invoice for an order
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function generateInvoice($id)
    {
        $user = Auth::user();

        // Different query based on user role
        if ($user->isAdmin() || $user->isWarehouse()) {
            // Admin or warehouse staff can view any invoice
            $order = Order::with(['items.product', 'items.variant', 'user.franchiseeProfile'])
                ->findOrFail($id);
        } else {
            // Regular franchisee can only see their own orders
            $order = Order::where('user_id', $user->id)
                ->with(['items.product', 'items.variant', 'user.franchiseeProfile'])
                ->findOrFail($id);
        }

        // Only allow downloads for orders that are approved or beyond
        if (!in_array($order->status, ['approved', 'packed', 'shipped', 'delivered'])) {
            return redirect()->back()->with('error', 'Invoice is only available for approved or completed orders.');
        }

        // Generate invoice number if not already set
        $invoiceNumber = $order->invoice_number ?? config('company.invoice_prefix', 'INV-') . $order->id . '-' . date('Ymd');

        // Make sure we have the franchisee profile
        if (!$order->relationLoaded('user.franchiseeProfile')) {
            $order->load(['user.franchiseeProfile']);
        }

        // Get the admin profile for company information
        $adminUser = \App\Models\User::whereHas('role', function($q) {
            $q->where('name', 'admin');
        })->first();

        $adminDetail = null;
        if ($adminUser) {
            $adminDetail = $adminUser->adminDetail;
        }

        // Get current date in the format needed for the invoice
        $currentDate = date('F d, Y');

        // Get due date (30 days from now by default)
        $dueDate = date('F d, Y', strtotime('+30 days'));

        // Return an HTML view of the invoice with print styling
        return view('franchisee.invoice-print', [
            'order' => $order,
            'invoiceNumber' => $invoiceNumber,
            'adminDetail' => $adminDetail,
            'currentDate' => $currentDate,
            'dueDate' => $dueDate
        ]);
    }
}