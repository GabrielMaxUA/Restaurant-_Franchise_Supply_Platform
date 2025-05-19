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
     *
     * @param InventoryService $inventoryService
     * @return void
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
     * Display the cart contents.
     * Supports both web and API requests.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart();
        $cartItems = [];
        $total = 0;
        
        // Eager load related models for performance
        $items = $cart->items()->with(['product', 'variant', 'product.images'])->get();
        
        foreach ($items as $item) {
            if ($item->variant_id) {
                $variant = $item->variant;
                $product = $item->product;
                
                // UPDATED: Use variant's price_adjustment directly as the price
                $price = $variant->price_adjustment;
                
                $itemData = [
                    'id' => $item->id,
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $item->quantity,
                    'price' => $price,
                    'subtotal' => $price * $item->quantity
                ];
                
                // For API responses, include only necessary product and variant data
                if ($request->expectsJson() || $request->wantsJson()) {
                    $itemData['product'] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description,
                        'base_price' => $product->base_price,
                        'image_url' => $product->images->isNotEmpty()
                          ? asset('storage/' . $product->images->first()->image_url)
                          : null

                    ];
                    
                    if ($variant) {
                        $itemData['variant'] = [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'price_adjustment' => $variant->price_adjustment,
                            'inventory_count' => $variant->inventory_count
                        ];
                    }
                }
                
                $cartItems[] = $itemData;
                $total += $price * $item->quantity;
            } else {
                $product = $item->product;
                if ($product) {
                    $itemData = [
                        'id' => $item->id,
                        'product' => $product,
                        'variant' => null,
                        'quantity' => $item->quantity,
                        'price' => $product->base_price,
                        'subtotal' => $product->base_price * $item->quantity
                    ];
                    
                    // For API responses, include only necessary product data
                    if ($request->expectsJson() || $request->wantsJson()) {
                        $itemData['product'] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'description' => $product->description,
                            'base_price' => $product->base_price,
                            'image_url' => $product->images->isNotEmpty()
                              ? asset('storage/' . $product->images->first()->image_url)
                              : null
                        ];
                    }
                    
                    $cartItems[] = $itemData;
                    $total += $product->base_price * $item->quantity;
                }
            }
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
     * Supports both web and API requests.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1'
        ]);
        
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $quantity = $request->quantity;
        
        try {
            // Begin database transaction for inventory check
            DB::beginTransaction();
            
            // Load product with variants using for update lock to prevent race conditions
            $product = Product::with(['variants'])->lockForUpdate()->find($productId);
            if (!$product) {
                DB::rollBack();
                if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 404);
                }
                return redirect()->back()->with('error', 'Product not found.');
            }
            
            // If a variant is specified, check variant inventory with locking
            if ($variantId) {
                $variant = ProductVariant::lockForUpdate()->find($variantId);
                $availableInventory = $variant ? $variant->inventory_count : 0;
                
                if (!$variant || $availableInventory < $quantity) {
                    DB::rollBack();
                    if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Selected variant is out of stock or has insufficient inventory.',
                            'remaining_inventory' => $availableInventory,
                            'requested_quantity' => $quantity,
                            'product_cart_quantity' => 0
                        ], 400);
                    }
                    return redirect()->back()->with('error', 'Selected variant is out of stock or has insufficient inventory.');
                }
            } else {
                // For main product, check if it has inventory
                $availableInventory = $product->inventory_count;
                
                if ($availableInventory < $quantity) {
                    // Check if any variants have inventory
                    $hasInStockVariants = false;
                    $availableVariants = [];
                    
                    // Lock all variants for consistent inventory check
                    $variants = ProductVariant::where('product_id', $productId)
                        ->lockForUpdate()
                        ->get();
                    
                    foreach ($variants as $variant) {
                        if ($variant->inventory_count > 0) {
                            $hasInStockVariants = true;
                            $availableVariants[] = [
                                'id' => $variant->id,
                                'name' => $variant->name,
                                'inventory' => $variant->inventory_count
                            ];
                        }
                    }
                    
                    DB::rollBack();
                    
                    if ($hasInStockVariants) {
                        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'This product is out of stock. Please select an available variant.',
                                'variants_available' => true,
                                'available_variants' => $availableVariants,
                                'product_id' => $productId
                            ], 400);
                        }
                        return redirect()->back()->with('error', 'This product is out of stock. Please select a variant.');
                    } else {
                        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Product is out of stock.',
                                'remaining_inventory' => $availableInventory,
                                'requested_quantity' => $quantity,
                                'product_cart_quantity' => 0
                            ], 400);
                        }
                        return redirect()->back()->with('error', 'Product is out of stock.');
                    }
                }
            }
            
            // Get user's cart
            $cart = $this->getOrCreateCart();
            
            // Check if this product/variant is already in the cart
            $query = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId);
                
            if ($variantId) {
                $query->where('variant_id', $variantId);
            } else {
                $query->whereNull('variant_id');
            }
            
            $existingItem = $query->first();
            $currentCartQuantity = 0;
            
            // If the product is already in the cart, update the quantity
            if ($existingItem) {
                $currentCartQuantity = $existingItem->quantity;
                $newQuantity = $currentCartQuantity + $quantity;
                
                // Check if the new quantity exceeds available stock
                if ($variantId) {
                    $variant = ProductVariant::lockForUpdate()->find($variantId);
                    $availableInventory = $variant ? $variant->inventory_count : 0;
                    
                    if (!$variant || $availableInventory < $newQuantity) {
                        DB::rollBack();
                        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Adding this quantity would exceed available inventory.',
                                'remaining_inventory' => ($availableInventory - $currentCartQuantity),
                                'requested_quantity' => $quantity,
                                'product_cart_quantity' => $currentCartQuantity
                            ], 400);
                        }
                        return redirect()->back()->with('error', 'Adding this quantity would exceed available inventory.');
                    }
                } else {
                    $availableInventory = $product ? $product->inventory_count : 0;
                    
                    if (!$product || $availableInventory < $newQuantity) {
                        DB::rollBack();
                        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Adding this quantity would exceed available inventory.',
                                'remaining_inventory' => ($availableInventory - $currentCartQuantity),
                                'requested_quantity' => $quantity,
                                'product_cart_quantity' => $currentCartQuantity
                            ], 400);
                        }
                        return redirect()->back()->with('error', 'Adding this quantity would exceed available inventory.');
                    }
                }
                
                // Update the quantity
                $existingItem->quantity = $newQuantity;
                $existingItem->save();
            } else {
                // Add to cart as a new item
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity
                ]);
            }
            
            // Commit the transaction after successfully adding to cart
            DB::commit();
            
            // Get current cart quantity for this product
            $finalCartQuantity = $existingItem ? $newQuantity : $quantity;
            
            // Calculate remaining inventory after adding to cart
            $totalInventory = $variantId 
                ? ($variant ? $variant->inventory_count : 0) 
                : ($product ? $product->inventory_count : 0);
            
            // Calculate actual remaining inventory (total - what's in cart)
            $remainingInventory = $totalInventory - $finalCartQuantity;
            
            // Get cart count
            $cartCount = $cart->items()->count();
            
            // Return success response for JSON or AJAX requests
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product added to cart successfully.',
                    'cart_count' => $cartCount,
                    'remaining_inventory' => $remainingInventory,
                    'product_cart_quantity' => $finalCartQuantity
                ]);
            }
            
            return redirect()->back()->with('success', 'Product added to cart successfully.');
        
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            
            \Log::error('Error adding to cart: ' . $e->getMessage(), [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add product to cart: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to add product to cart: ' . $e->getMessage());
        }
    }
    
    /**
     * Update cart item quantity.
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:cart_items,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);
        
        $cart = $this->getOrCreateCart();
        $success = true;
        $message = 'Cart updated successfully.';
        
        foreach ($request->items as $item) {
            $itemId = $item['id'];
            $quantity = $item['quantity'];
            
            // Get the cart item and check if it belongs to the user's cart
            $cartItem = CartItem::where('id', $itemId)
                ->where('cart_id', $cart->id)
                ->first();
            
            if ($cartItem) {
                // Check inventory before updating
                if ($cartItem->variant_id) {
                    $variant = ProductVariant::find($cartItem->variant_id);
                    if ($variant && $variant->inventory_count >= $quantity) {
                        $cartItem->quantity = $quantity;
                        $cartItem->save();
                    } else {
                        $success = false;
                        $message = 'Insufficient inventory for ' . ($variant ? $variant->name : 'selected variant');
                        break;
                    }
                } else {
                    $product = Product::find($cartItem->product_id);
                    if ($product && $product->inventory_count >= $quantity) {
                        $cartItem->quantity = $quantity;
                        $cartItem->save();
                    } else {
                        $success = false;
                        $message = 'Insufficient inventory for ' . ($product ? $product->name : 'selected product');
                        break;
                    }
                }
            }
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                'cart_count' => $cart->items()->count()
            ], $success ? 200 : 400);
        }
        
        if ($success) {
            return redirect()->back()->with('success', $message);
        } else {
            return redirect()->back()->with('error', $message);
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
        
        if ($cartItem) {
            // If quantity is specified and less than current quantity, reduce quantity
            if ($quantity !== null && $quantity > 0 && $quantity < $cartItem->quantity) {
                $cartItem->quantity -= $quantity;
                $cartItem->save();
                $message = 'Item quantity reduced.';
            } else {
                // Otherwise, remove the item completely
                $cartItem->delete();
                $message = 'Item removed from cart.';
            }
            
            // Calculate total items count for badge display
            $totalItems = $cart->items()->sum('quantity');
            $cartCount = $cart->items()->count();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => $cartCount,
                    'total_items' => $totalItems
                ]);
            }
            
            return redirect()->back()->with('success', $message);
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in cart.'
            ], 404);
        }
        
        return redirect()->back()->with('error', 'Item not found in cart.');
    }
    
    /**
     * Clear the entire cart.
     */
    public function clearCart()
    {
        $cart = $this->getOrCreateCart();
        $cart->items()->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully.',
                'cart_count' => 0
            ]);
        }
        
        return redirect()->back()->with('success', 'Cart cleared successfully.');
    }
    
    /**
     * Get the current cart count for AJAX requests.
     */
    public function getCartCount()
    {
        $cart = $this->getOrCreateCart();
        $count = $cart->items()->count();
        
        return response()->json([
            'count' => $count
        ]);
    }
    
    /**
     * Show the checkout form.
     * UPDATED: Uses variant price_adjustment directly as the price
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
              if ($item->variant_id) {
                  $variant = $item->variant;
                  $product = $item->product;
                  $price = $variant->price_adjustment;
              
                  $cartItems[] = [
                      'id' => $item->id,
                      'product' => $product,
                      'variant' => $variant,
                      'quantity' => $item->quantity,
                      'price' => $price,
                      'subtotal' => $price * $item->quantity
                  ];
                
                  $total += $price * $item->quantity;
              } else {
                  $product = $item->product;
                  if ($product) {
                      $cartItems[] = [
                          'id' => $item->id,
                          'product' => $product,
                          'variant' => null,
                          'quantity' => $item->quantity,
                          'price' => $product->base_price,
                          'subtotal' => $product->base_price * $item->quantity
                      ];
                      $total += $product->base_price * $item->quantity;
                  }
              }
          }
        
            if ($request->expectsJson()) 
            {
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
     * Process the order.
     * UPDATED: Uses variant price_adjustment directly as the price
     */
    public function placeOrder(Request $request)
    {
        \Log::info('placeOrder called with input:', $request->all());
    
        $request->validate([
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_zip' => 'required|string|max:20',
            'delivery_preference' => 'required|string',
        ]);
    
        $cart = $this->getOrCreateCart();
        $items = $cart->items()->with(['product', 'variant'])->get();
    
        if ($items->isEmpty()) {
            \Log::info('Cart is empty at placeOrder for user: ' . Auth::id());
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty.'
                ], 400);
            }
    
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add some products before checkout.');
        }
    
        $total = 0;
        $orderItems = [];
        $inventoryIssues = [];
    
        foreach ($items as $item) {
            if ($item->variant_id) {
                $variant = $item->variant;
                $product = $item->product;
    
                if (!$product || !$variant || $variant->inventory_count < $item->quantity) {
                    $availableQty = $variant ? $variant->inventory_count : 0;
                    $inventoryIssues[] = "Only {$availableQty} units of '{$product->name} ({$variant->name})' available.";
                    continue;
                }
    
                $price = $variant->price_adjustment;
                $subtotal = $price * $item->quantity;
                $total += $subtotal;
    
                $orderItems[] = [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => $item->quantity,
                    'price' => $price
                ];
            } else {
                $product = $item->product;
    
                if (!$product || $product->inventory_count < $item->quantity) {
                    $availableQty = $product ? $product->inventory_count : 0;
                    $inventoryIssues[] = "Only {$availableQty} units of '{$product->name}' available.";
                    continue;
                }
    
                $subtotal = $product->base_price * $item->quantity;
                $total += $subtotal;
    
                $orderItems[] = [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'quantity' => $item->quantity,
                    'price' => $product->base_price
                ];
            }
        }
    
        if (!empty($inventoryIssues)) {
            \Log::warning('Inventory issues at placeOrder:', $inventoryIssues);
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory issue(s) occurred.',
                    'details' => $inventoryIssues
                ], 400);
            }
    
            return redirect()->route('franchisee.cart')
                ->with('error', implode('<br>', $inventoryIssues));
        }
    
        if (empty($orderItems)) {
            \Log::warning('No valid orderItems found');
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid items in your cart.'
                ], 400);
            }
    
            return redirect()->route('franchisee.cart')
                ->with('error', 'No valid items in your cart.');
        }
    
        $tax = $total * 0.08;
        $shippingCost = $request->delivery_preference === 'express' ? 15.00 : 0;
        $finalTotal = $total + $tax + $shippingCost;
    
        DB::beginTransaction();
    
        try {
            $order = new Order([
                'user_id' => Auth::id(),
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
                'manager_name' => $request->input('manager_name', 'Default Manager'),
                'contact_phone' => $request->input('contact_phone', Auth::user()->phone ?? 'N/A'),
            ]);
            $order->save();
    
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
    
                $this->inventoryService->decreaseInventory(
                    $item['product_id'],
                    $item['quantity'],
                    $item['variant_id']
                );
            }
    
            $cart->items()->delete();
            DB::commit();
    
            \Log::info("Order #{$order->id} placed successfully by user #" . Auth::id());
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully.',
                    'order_id' => $order->id,
                    'total' => $finalTotal
                ]);
            }
    
            return redirect()->route('franchisee.orders.details', $order->id)
                ->with('success', 'Your order has been placed successfully!');
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Order placement failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request' => $request->all()
            ]);
    
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to place order.',
                    'error' => $e->getMessage()
                ], 500);
            }
    
            return redirect()->back()
                ->with('error', 'There was a problem processing your order. Please try again.');
        }
    }
    
    
  
}