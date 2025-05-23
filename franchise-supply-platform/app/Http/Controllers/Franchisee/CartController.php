<?php

namespace App\Http\Controllers\Franchisee;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use App\Models\OrderNotification;
use App\Services\InventoryService;
use App\Services\EmailNotificationService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $inventoryService;

    /**
     * Create a new controller instance.
     */
    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }
    
    /**
     * Get or create the current user's cart
     */
    private function getOrCreateCart()
    {
        $userId = Auth::id();
        $cart = Cart::firstOrCreate(['user_id' => $userId]);
        return $cart;
    }
    
    /**
     * Calculate the correct price for an item (product or variant)
     */
    private function calculateItemPrice($product, $variant = null)
    {
        if ($variant) {
            // For variants, use price_adjustment as the actual price
            return (float) $variant->price_adjustment;
        }
        
        return (float) $product->base_price;
    }
    
    /**
     * Get inventory count for an item (product or variant)
     */
    private function getInventoryCount($product, $variant = null)
    {
        if ($variant) {
            return (int) $variant->inventory_count;
        }
        
        return (int) $product->inventory_count;
    }
    
    /**
     * Display the cart contents.
     */
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $cartItems = [];
        $total = 0;
        
        // Eager load related models for performance
        $items = $cart->items()->with(['product', 'variant', 'product.images'])->get();
        
        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            
            if (!$product) {
                continue; // Skip items with missing products
            }
            
            $price = $this->calculateItemPrice($product, $variant);
            $subtotal = $price * $item->quantity;
            
            $itemData = [
                'id' => $item->id,
                'product_id' => $product->id,
                'variant_id' => $variant ? $variant->id : null,
                'quantity' => $item->quantity,
                'price' => $price,
                'subtotal' => $subtotal,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'base_price' => $product->base_price,
                    'inventory_count' => $product->inventory_count,
                    'image_url' => $product->images->isNotEmpty()
                      ? asset('storage/' . $product->images->first()->image_url)
                      : null
                ],
                'variant' => $variant ? [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'price_adjustment' => $variant->price_adjustment,
                    'inventory_count' => $variant->inventory_count
                ] : null
            ];
            
            // For web responses, include full models
            if (!($request->expectsJson() || $request->wantsJson())) {
                $itemData['product'] = $product;
                $itemData['variant'] = $variant;
            }
            
            $cartItems[] = $itemData;
            $total += $subtotal;
        }
        
        // Check if this is an API request
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'cart_items' => $cartItems,
                'total' => $total,
                'items_count' => count($cartItems)
            ]);
        }
        
        // Web response
        return view('franchisee.cart', compact('cartItems', 'total'));
    }
    
    /**
     * Add a product to the cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'check_inventory' => 'boolean'
        ]);
        
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $requestedQuantity = $request->quantity;
        $checkInventory = $request->boolean('check_inventory', true);
        
        try {
            DB::beginTransaction();
            
            // Load product with variants
            $product = Product::with(['variants'])->lockForUpdate()->find($productId);
            if (!$product) {
                DB::rollBack();
                return $this->errorResponse($request, 'Product not found.', 404);
            }
            
            $variant = null;
            if ($variantId) {
                $variant = ProductVariant::lockForUpdate()->find($variantId);
                if (!$variant) {
                    DB::rollBack();
                    return $this->errorResponse($request, 'Variant not found.', 404);
                }
            }
            
            // Get current inventory
            $availableInventory = $this->getInventoryCount($product, $variant);
            
            // Get user's cart and check existing quantity
            $cart = $this->getOrCreateCart();
            $existingItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->where(function($query) use ($variantId) {
                    if ($variantId) {
                        $query->where('variant_id', $variantId);
                    } else {
                        $query->whereNull('variant_id');
                    }
                })->first();
            
            $currentCartQuantity = $existingItem ? $existingItem->quantity : 0;
            $totalRequested = $currentCartQuantity + $requestedQuantity;
            
            // Inventory checking
            $warnings = [];
            $finalQuantity = $requestedQuantity;
            $wasAdjusted = false;
            
            if ($checkInventory && $availableInventory < $totalRequested) {
                $maxCanAdd = max(0, $availableInventory - $currentCartQuantity);
                
                if ($maxCanAdd <= 0) {
                    DB::rollBack();
                    $message = $currentCartQuantity > 0 
                        ? "You already have all available stock ({$currentCartQuantity}) in your cart"
                        : 'This item is out of stock';
                    
                    return $this->errorResponse($request, $message, 400, [
                        'inventory_limited' => true,
                        'max_available' => 0,
                        'current_cart_quantity' => $currentCartQuantity,
                        'total_inventory' => $availableInventory
                    ]);
                }
                
                // Adjust quantity to what's available
                $finalQuantity = $maxCanAdd;
                $wasAdjusted = true;
                
                $itemName = $variant ? "{$product->name} ({$variant->name})" : $product->name;
                $warnings[] = "Only {$availableInventory} units available in stock. " .
                    ($currentCartQuantity > 0 
                        ? "Your cart already has {$currentCartQuantity} of {$itemName}. Only {$finalQuantity} more added."
                        : "Only {$finalQuantity} added to cart.");
            }
            
            // Add or update cart item
            if ($existingItem) {
                $existingItem->quantity += $finalQuantity;
                $existingItem->save();
                $newCartQuantity = $existingItem->quantity;
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $finalQuantity
                ]);
                $newCartQuantity = $finalQuantity;
            }
            
            DB::commit();
            
            // Calculate remaining inventory
            $remainingInventory = max(0, $availableInventory - $newCartQuantity);
            $cartCount = $cart->items()->count();
            
            $message = $wasAdjusted 
                ? "Added {$finalQuantity} item(s) to cart (inventory adjusted)"
                : "Added {$finalQuantity} item(s) to cart successfully";
            
            $responseData = [
                'success' => true,
                'message' => $message,
                'cart_count' => $cartCount,
                'items_count' => $cartCount,
                'remaining_inventory' => $remainingInventory,
                'product_cart_quantity' => $newCartQuantity,
                'was_adjusted' => $wasAdjusted,
                'requested_quantity' => $requestedQuantity,
                'actual_quantity_added' => $finalQuantity
            ];
            
            if (!empty($warnings)) {
                $responseData['warnings'] = $warnings;
            }
            
            return $this->successResponse($request, $responseData);
        
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error adding to cart: ' . $e->getMessage(), [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $requestedQuantity,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->errorResponse($request, 'Failed to add product to cart: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update cart item quantity (bulk update).
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:cart_items,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);
        
        $cart = $this->getOrCreateCart();
        $warnings = [];
        $success = true;
        
        DB::beginTransaction();
        
        try {
            foreach ($request->items as $item) {
                $itemId = $item['id'];
                $newQuantity = $item['quantity'];
                
                $cartItem = CartItem::where('id', $itemId)
                    ->where('cart_id', $cart->id)
                    ->with(['product', 'variant'])
                    ->lockForUpdate()
                    ->first();
                
                if (!$cartItem) {
                    continue;
                }
                
                $availableInventory = $this->getInventoryCount($cartItem->product, $cartItem->variant);
                
                if ($newQuantity > $availableInventory) {
                    $adjustedQuantity = max(1, $availableInventory);
                    $cartItem->quantity = $adjustedQuantity;
                    $cartItem->save();
                    
                    $itemName = $cartItem->variant 
                        ? "{$cartItem->product->name} ({$cartItem->variant->name})"
                        : $cartItem->product->name;
                    
                    $warnings[] = "Quantity for {$itemName} adjusted to {$adjustedQuantity} (maximum available)";
                } else {
                    $cartItem->quantity = $newQuantity;
                    $cartItem->save();
                }
            }
            
            DB::commit();
            
            $responseData = [
                'success' => true,
                'message' => 'Cart updated successfully',
                'cart_count' => $cart->items()->count(),
                'items_count' => $cart->items()->count()
            ];
            
            if (!empty($warnings)) {
                $responseData['warnings'] = $warnings;
            }
            
            return $this->successResponse($request, $responseData);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse($request, 'Failed to update cart: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Update a single cart item quantity.
     */
    public function updateCartItemQuantity(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer|exists:cart_items,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $itemId = $request->item_id;
        $requestedQuantity = $request->quantity;
        
        DB::beginTransaction();
        
        try {
            $cart = $this->getOrCreateCart();
            
            $cartItem = CartItem::where('id', $itemId)
                ->where('cart_id', $cart->id)
                ->with(['product', 'variant'])
                ->lockForUpdate()
                ->first();
            
            if (!$cartItem) {
                DB::rollBack();
                return $this->errorResponse($request, 'Cart item not found.', 404);
            }
            
            $availableInventory = $this->getInventoryCount($cartItem->product, $cartItem->variant);
            $itemName = $cartItem->variant 
                ? "{$cartItem->product->name} ({$cartItem->variant->name})"
                : $cartItem->product->name;
            
            $finalQuantity = $requestedQuantity;
            $wasAdjusted = false;
            $itemRemoved = false;
            $message = '';
            
            if ($requestedQuantity > $availableInventory) {
                if ($availableInventory > 0) {
                    $finalQuantity = $availableInventory;
                    $cartItem->quantity = $finalQuantity;
                    $cartItem->save();
                    $wasAdjusted = true;
                    $message = "Quantity for {$itemName} adjusted to {$finalQuantity} (maximum available)";
                } else {
                    $cartItem->delete();
                    $itemRemoved = true;
                    $finalQuantity = 0;
                    $message = "{$itemName} removed from cart (out of stock)";
                }
            } else {
                $cartItem->quantity = $finalQuantity;
                $cartItem->save();
                $message = "Quantity for {$itemName} updated to {$finalQuantity}";
            }
            
            DB::commit();
            
            $cartCount = $cart->items()->count();
            $remainingInventory = max(0, $availableInventory - $finalQuantity);
            
            return $this->successResponse($request, [
                'success' => true,
                'message' => $message,
                'cart_count' => $cartCount,
                'items_count' => $cartCount,
                'final_quantity' => $finalQuantity,
                'remaining_inventory' => $remainingInventory,
                'was_adjusted' => $wasAdjusted,
                'item_removed' => $itemRemoved
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error updating cart item quantity: ' . $e->getMessage(), [
                'item_id' => $itemId,
                'quantity' => $requestedQuantity,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse($request, 'Failed to update cart item: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Remove an item from the cart or reduce its quantity.
     */
    public function removeFromCart(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity', null);
        
        $cart = $this->getOrCreateCart();
        $cartItem = CartItem::where('id', $itemId)
            ->where('cart_id', $cart->id)
            ->first();
        
        if (!$cartItem) {
            return $this->errorResponse($request, 'Item not found in cart.', 404);
        }
        
        $message = '';
        
        if ($quantity !== null && $quantity > 0 && $quantity < $cartItem->quantity) {
            $cartItem->quantity -= $quantity;
            $cartItem->save();
            $message = 'Item quantity reduced.';
        } else {
            $cartItem->delete();
            $message = 'Item removed from cart.';
        }
        
        $cartCount = $cart->items()->count();
        $totalItems = $cart->items()->sum('quantity');
        
        return $this->successResponse($request, [
            'success' => true,
            'message' => $message,
            'cart_count' => $cartCount,
            'items_count' => $cartCount,
            'total_items' => $totalItems
        ]);
    }
    
    /**
     * Clear the entire cart.
     */
    public function clearCart(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        
        return $this->successResponse($request, [
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'cart_count' => 0,
            'items_count' => 0
        ]);
    }
    
    /**
     * Get the current cart count for AJAX requests.
     */
    public function getCartCount()
    {
        $cart = $this->getOrCreateCart();
        $count = $cart->items()->count();
        
        return response()->json([
            'count' => $count,
            'items_count' => $count
        ]);
    }
    
    /**
     * Show the checkout form.
     */
    public function checkout(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $items = $cart->items()->with(['product', 'variant', 'product.images'])->get();

        if ($items->isEmpty()) {
            \Log::info('Checkout called but cart is empty for user: ' . Auth::id());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty. Please add some products before checkout.'
                ], 400);
            }

            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add some products before checkout.');
        }

        $cartItems = [];
        $total = 0;
      
        foreach ($items as $item) {
            $product = $item->product;
            $variant = $item->variant;
            
            if (!$product) {
                continue;
            }
            
            $price = $this->calculateItemPrice($product, $variant);
            $subtotal = $price * $item->quantity;
            
            $cartItems[] = [
                'id' => $item->id,
                'product' => $product,
                'variant' => $variant,
                'quantity' => $item->quantity,
                'price' => $price,
                'subtotal' => $subtotal
            ];
            
            $total += $subtotal;
        }
      
        if ($request->expectsJson()) {
            \Log::info('Checkout API data:', [
                'user_id' => Auth::id(),
                'cart_items' => $cartItems,
                'total' => $total
            ]);
              
            return response()->json([
                'success' => true,
                'cart_items' => $cartItems,
                'total' => $total
            ]);
        }
        
        $franchisee = Auth::user()->franchisee;
        return view('franchisee.checkout', compact('cartItems', 'total', 'franchisee'));
    }
    
   /**
 * Process the order - Updated version with better error handling and response format
 */
public function placeOrder(Request $request)
{
    \Log::info('placeOrder called with input:', $request->all());

    // Validate the incoming request
    $validator = \Validator::make($request->all(), [
        'shipping_address' => 'required|string|max:255',
        'shipping_city' => 'required|string|max:100',
        'shipping_state' => 'required|string|max:100',
        'shipping_zip' => 'required|string|max:20',
        'delivery_preference' => 'required|string|in:standard,express,scheduled',
        'delivery_date' => 'nullable|date|after:today',
        'notes' => 'nullable|string|max:1000',
    ]);

    if ($validator->fails()) {
        \Log::warning('Validation failed for placeOrder:', $validator->errors()->toArray());
        return $this->errorResponse($request, 'Validation failed.', 422, [
            'errors' => $validator->errors()->toArray()
        ]);
    }

    $cart = $this->getOrCreateCart();
    $items = $cart->items()->with(['product', 'variant'])->get();

    if ($items->isEmpty()) {
        \Log::info('Cart is empty at placeOrder for user: ' . Auth::id());
        return $this->errorResponse($request, 'Your cart is empty.', 400);
    }

    $total = 0;
    $orderItems = [];
    $inventoryIssues = [];

    // Process each cart item and check inventory
    foreach ($items as $item) {
        $product = $item->product;
        $variant = $item->variant;
        
        if (!$product) {
            \Log::warning('Product not found for cart item: ' . $item->id);
            continue;
        }
        
        $availableInventory = $this->getInventoryCount($product, $variant);
        
        if ($availableInventory < $item->quantity) {
            $itemName = $variant ? "{$product->name} ({$variant->name})" : $product->name;
            $inventoryIssues[] = "Only {$availableInventory} units of '{$itemName}' available, but {$item->quantity} requested.";
            \Log::warning("Inventory issue for {$itemName}: available={$availableInventory}, requested={$item->quantity}");
            continue;
        }

        $price = $this->calculateItemPrice($product, $variant);
        $subtotal = $price * $item->quantity;
        $total += $subtotal;

        $orderItems[] = [
            'product_id' => $product->id,
            'variant_id' => $variant ? $variant->id : null,
            'quantity' => $item->quantity,
            'price' => $price
        ];
    }

    // Check for inventory issues
    if (!empty($inventoryIssues)) {
        \Log::warning('Inventory issues at placeOrder:', $inventoryIssues);
        return $this->errorResponse($request, 'Some items in your cart are no longer available in the requested quantities.', 400, [
            'details' => $inventoryIssues
        ]);
    }

    if (empty($orderItems)) {
        \Log::warning('No valid orderItems found');
        return $this->errorResponse($request, 'No valid items in your cart.', 400);
    }

    // Calculate totals
    $tax = $total * 0.08; // 8% tax
    $shippingCost = 0;
    
    if ($request->delivery_preference === 'express') {
        $shippingCost = 15.00;
    }
    
    $finalTotal = $total + $tax + $shippingCost;

    // Generate order number
    $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());

    DB::beginTransaction();

    try {
        // Create the order
        $order = new Order([
            'user_id' => Auth::id(),
            'order_number' => $orderNumber,
            'status' => 'pending',
            'total_amount' => $finalTotal,
            'shipping_address' => $request->shipping_address,
            'shipping_city' => $request->shipping_city,
            'shipping_state' => $request->shipping_state,
            'shipping_zip' => $request->shipping_zip,
            'delivery_date' => $request->delivery_date ?? now()->addDays(3)->toDateString(),
            'delivery_time' => $request->input('delivery_time', 'morning'),
            'delivery_preference' => $request->delivery_preference,
            'shipping_cost' => $shippingCost,
            'notes' => $request->notes ?? '',
            'manager_name' => $request->input('manager_name', Auth::user()->name ?? 'Default Manager'),
            'contact_phone' => $request->input('contact_phone', Auth::user()->phone ?? 'N/A'),
            'created_at' => now(),
            'updated_at' => now()
        ]);
        $order->save();

        // Create order items and update inventory
        foreach ($orderItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Decrease inventory
            $this->inventoryService->decreaseInventory(
                $item['product_id'],
                $item['quantity'],
                $item['variant_id']
            );
        }

        // Clear the cart
        $cart->items()->delete();
        
        DB::commit();

        \Log::info("Order #{$order->id} ({$orderNumber}) placed successfully by user #" . Auth::id() . " with total: $" . $finalTotal);

        // Send success response
        return $this->successResponse($request, [
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order->id,
            'order_number' => $orderNumber,
            'total' => $finalTotal,
            'subtotal' => $total,
            'tax' => $tax,
            'shipping_cost' => $shippingCost,
            'items_count' => count($orderItems)
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Order placement failed:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'request' => $request->all()
        ]);

        return $this->errorResponse($request, 'Failed to place order. Please try again.', 500, [
            'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
        ]);
    }
}
    
    /**
     * Helper method for success responses
     */
    private function successResponse($request, $data)
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json($data);
        }
        
        return redirect()->back()->with('success', $data['message'] ?? 'Operation successful');
    }
    
    /**
     * Helper method for error responses
     */
    private function errorResponse($request, $message, $statusCode = 400, $additionalData = [])
    {
        $responseData = array_merge([
            'success' => false,
            'message' => $message
        ], $additionalData);
        
        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json($responseData, $statusCode);
        }
        
        return redirect()->back()->with('error', $message);
    }
}