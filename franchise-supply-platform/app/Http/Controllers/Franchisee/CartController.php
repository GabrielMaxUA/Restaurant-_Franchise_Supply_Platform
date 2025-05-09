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
use App\Services\InventoryService;
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
     * UPDATED: Uses variant price_adjustment directly as the price
     */
    public function index()
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
            'quantity' => 'required|integer|min:1'
        ]);
        
        $productId = $request->product_id;
        $variantId = $request->variant_id;
        $quantity = $request->quantity;
        
        // Load product with variants
        $product = Product::with(['variants'])->find($productId);
        if (!$product) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }
            return redirect()->back()->with('error', 'Product not found.');
        }
        
        // If a variant is specified, check variant inventory
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            $availableInventory = $variant ? $variant->inventory_count : 0;
            
            if (!$variant || $availableInventory < $quantity) {
                if ($request->ajax()) {
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
                
                foreach ($product->variants as $variant) {
                    if ($variant->inventory_count > 0) {
                        $hasInStockVariants = true;
                        $availableVariants[] = [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'inventory' => $variant->inventory_count
                        ];
                    }
                }
                
                if ($hasInStockVariants) {
                    if ($request->ajax()) {
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
                    if ($request->ajax()) {
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
                $variant = ProductVariant::find($variantId);
                $availableInventory = $variant ? $variant->inventory_count : 0;
                
                if (!$variant || $availableInventory < $newQuantity) {
                    if ($request->ajax()) {
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
                    if ($request->ajax()) {
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
        
        // Return success response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'cart_count' => $cartCount,
                'remaining_inventory' => $remainingInventory,
                'product_cart_quantity' => $finalCartQuantity
            ]);
        }
        
        return redirect()->back()->with('success', 'Product added to cart successfully.');
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
    public function checkout()
    {
        $cart = $this->getOrCreateCart();
        $items = $cart->items()->with(['product', 'variant', 'product.images'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add some products before checkout.');
        }
        
        $cartItems = [];
        $total = 0;
        
        // Process cart items with updated pricing model
        foreach ($items as $item) {
            if ($item->variant_id) {
                $variant = $item->variant;
                $product = $item->product;
                
                // UPDATED: Use variant's price_adjustment directly as the price
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
        
        // Get user's franchisee information
        $franchisee = Auth::user()->franchisee;
        
        return view('franchisee.checkout', compact('cartItems', 'total', 'franchisee'));
    }
    
    /**
     * Process the order.
     * UPDATED: Uses variant price_adjustment directly as the price
     */
    public function placeOrder(Request $request)
    {
        // Validate the request
        $request->validate([
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_zip' => 'required|string|max:20',
            'delivery_preference' => 'required|string',
        ]);

        // Get cart items
        $cart = $this->getOrCreateCart();
        $items = $cart->items()->with(['product', 'variant'])->get();
        
        if ($items->isEmpty()) {
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add some products before checkout.');
        }
        
        // Calculate total
        $total = 0;
        $orderItems = [];
        
        foreach ($items as $item) {
            if ($item->variant_id) {
                $variant = $item->variant;
                $product = $item->product;
                
                // UPDATED: Use variant's price_adjustment directly as the price
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
                if ($product) {
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
        }
        
        // Calculate tax (8%)
        $tax = $total * 0.08;
        
        // Add shipping cost if express delivery
        $shippingCost = 0;
        if ($request->delivery_preference === 'express') {
            $shippingCost = 15.00;
        }
        
        // Calculate final total
        $finalTotal = $total + $tax + $shippingCost;
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Create the order
            $order = new Order();
            $order->user_id = Auth::id();
            $order->status = 'pending';
            $order->total_amount = $finalTotal;
            $order->shipping_address = $request->shipping_address;
            $order->shipping_city = $request->shipping_city;
            $order->shipping_state = $request->shipping_state;
            $order->shipping_zip = $request->shipping_zip;
            
            // Default values for required fields
            $order->delivery_date = $request->delivery_date ?? date('Y-m-d', strtotime('+3 days'));
            $order->delivery_time = $request->input('delivery_time', 'morning');
            $order->delivery_preference = $request->delivery_preference;
            $order->shipping_cost = $shippingCost;
            $order->notes = $request->notes ?? '';
            $order->manager_name = $request->input('manager_name', 'Default Manager');
            $order->contact_phone = $request->input('contact_phone', Auth::user()->phone ?? 'No contact provided');
            
            // Save the order
            $order->save();
            
            // Create order items and update inventory
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
                
                // Update inventory using the InventoryService
                $inventoryResult = $this->inventoryService->decreaseInventory(
                    $item['product_id'],
                    $item['quantity'],
                    $item['variant_id']
                );
                
                if (!$inventoryResult) {
                    throw new \Exception('Failed to update inventory for product #' . $item['product_id']);
                }
            }
            
            // Clear the cart after successfully placing the order
            $cart->items()->delete();
            
            // Commit the transaction
            DB::commit();
            
            // Redirect to order details page
            return redirect()->route('franchisee.orders.details', $order->id)
                ->with('success', 'Your order has been placed successfully!');
                
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            
            // Log error
            \Log::error('Order placement failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            // Redirect back with error
            return redirect()->back()
                ->with('error', 'There was a problem processing your order. Please try again: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Debug method to check price calculations
     */
    public function debugCartPrices()
    {
        // Only allow in local or development environment
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }
        
        $cart = $this->getOrCreateCart();
        $items = $cart->items()->with(['product', 'variant'])->get();
        
        $debug = [];
        foreach ($items as $item) {
            $debug[] = [
                'item_id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product ? $item->product->name : 'No product',
                'product_base_price' => $item->product ? $item->product->base_price : 'N/A',
                'variant_id' => $item->variant_id,
                'variant_name' => $item->variant ? $item->variant->name : 'No variant',
                'variant_price_adjustment' => $item->variant ? $item->variant->price_adjustment : 'N/A',
                'used_price' => $item->variant_id 
                    ? ($item->variant ? $item->variant->price_adjustment : 'Variant not found')
                    : ($item->product ? $item->product->base_price : 'Product not found'),
                'quantity' => $item->quantity,
                'subtotal' => $item->variant_id 
                    ? ($item->variant ? $item->variant->price_adjustment * $item->quantity : 'Cannot calculate')
                    : ($item->product ? $item->product->base_price * $item->quantity : 'Cannot calculate')
            ];
        }
        
        return response()->json([
            'cart_items' => $debug
        ]);
    }
}