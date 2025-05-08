<?php

namespace App\Http\Controllers\Franchisee;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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
     * Display the cart contents.
     */
    public function index()
    {
        $cart = Session::get('cart', []);
        $cartItems = [];
        $total = 0;
        
        foreach ($cart as $itemId => $item) {
            // Handle both products and variants
            if (isset($item['variant_id'])) {
                $variant = ProductVariant::with('product')->find($item['variant_id']);
                if ($variant) {
                    $product = $variant->product;
                    $price = $product->base_price + $variant->price_adjustment;
                    $cartItems[] = [
                        'id' => $itemId,
                        'product' => $product,
                        'variant' => $variant,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'subtotal' => $price * $item['quantity']
                    ];
                    $total += $price * $item['quantity'];
                }
            } else {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $cartItems[] = [
                        'id' => $itemId,
                        'product' => $product,
                        'variant' => null,
                        'quantity' => $item['quantity'],
                        'price' => $product->base_price,
                        'subtotal' => $product->base_price * $item['quantity']
                    ];
                    $total += $product->base_price * $item['quantity'];
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
        
        // Get cart from session
        $cart = Session::get('cart', []);
        
        // Create a unique identifier for the product/variant combination
        $productKey = $variantId ? $productId . '_' . $variantId : $productId . '_0';
        
        // Check if this product/variant is already in the cart
        $existingItem = null;
        $existingItemId = null;
        
        foreach ($cart as $itemId => $item) {
            $itemProductId = $item['product_id'];
            $itemVariantId = isset($item['variant_id']) ? $item['variant_id'] : 0;
            $itemKey = $itemProductId . '_' . $itemVariantId;
            
            // If we found a match
            if ($itemKey === $productKey) {
                $existingItem = $item;
                $existingItemId = $itemId;
                break;
            }
        }
        
        // If the product is already in the cart, update the quantity
        if ($existingItem) {
            // Calculate the new total quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $currentCartQuantity = $existingItem['quantity'];
            
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
            $cart[$existingItemId]['quantity'] = $newQuantity;
        } else {
            // Generate a unique cart item ID
            $itemId = uniqid();
            
            // Add to cart as a new item
            if ($variantId) {
                $cart[$itemId] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'quantity' => $quantity
                ];
            } else {
                $cart[$itemId] = [
                    'product_id' => $productId,
                    'quantity' => $quantity
                ];
            }
        }
        
        // Save cart back to session
        Session::put('cart', $cart);
        
        // Get current cart quantity for this product
        $currentCartQuantity = $existingItem ? $newQuantity : $quantity;
        
        // Calculate remaining inventory after adding to cart
        $totalInventory = $variantId 
            ? ($variant ? $variant->inventory_count : 0) 
            : ($product ? $product->inventory_count : 0);
        
        // Calculate actual remaining inventory (total - what's in cart)
        $remainingInventory = $totalInventory - $currentCartQuantity;
        
        // Return success response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'cart_count' => count($cart),
                'remaining_inventory' => $remainingInventory,
                'product_cart_quantity' => $existingItem ? $newQuantity : $quantity
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
            'items.*.id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1'
        ]);
        
        $cart = Session::get('cart', []);
        
        foreach ($request->items as $item) {
            $itemId = $item['id'];
            $quantity = $item['quantity'];
            
            if (isset($cart[$itemId])) {
                // Check inventory before updating
                if (isset($cart[$itemId]['variant_id'])) {
                    $variant = ProductVariant::find($cart[$itemId]['variant_id']);
                    if ($variant && $variant->inventory_count >= $quantity) {
                        $cart[$itemId]['quantity'] = $quantity;
                    } else {
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Insufficient inventory for ' . ($variant ? $variant->name : 'selected variant')
                            ], 400);
                        }
                        return redirect()->back()->with('error', 'Insufficient inventory for ' . ($variant ? $variant->name : 'selected variant'));
                    }
                } else {
                    $product = Product::find($cart[$itemId]['product_id']);
                    if ($product && $product->inventory_count >= $quantity) {
                        $cart[$itemId]['quantity'] = $quantity;
                    } else {
                        if ($request->ajax()) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Insufficient inventory for ' . ($product ? $product->name : 'selected product')
                            ], 400);
                        }
                        return redirect()->back()->with('error', 'Insufficient inventory for ' . ($product ? $product->name : 'selected product'));
                    }
                }
            }
        }
        
        Session::put('cart', $cart);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully.',
                'cart_count' => count($cart)
            ]);
        }
        
        return redirect()->back()->with('success', 'Cart updated successfully.');
    }
    
    /**
     * Remove an item from the cart or reduce its quantity.
     */
    public function removeFromCart(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = $request->input('quantity', null);
        $cart = Session::get('cart', []);
        
        if (isset($cart[$itemId])) {
            // If quantity is specified and less than current quantity, reduce quantity
            if ($quantity !== null && $quantity > 0 && $quantity < $cart[$itemId]['quantity']) {
                $cart[$itemId]['quantity'] -= $quantity;
                $message = 'Item quantity reduced.';
            } else {
                // Otherwise, remove the item completely
                unset($cart[$itemId]);
                $message = 'Item removed from cart.';
            }
            
            Session::put('cart', $cart);
            
            // Calculate total items count for badge display
            $totalItems = 0;
            foreach ($cart as $item) {
                $totalItems += $item['quantity'];
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'cart_count' => count($cart),
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
        Session::forget('cart');
        
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
        $cart = Session::get('cart', []);
        
        return response()->json([
            'count' => count($cart)
        ]);
    }
    
    /**
     * Show the checkout form.
     */
    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add some products before checkout.');
        }
        
        $cartItems = [];
        $total = 0;
        
        // Process cart items similar to index method
        foreach ($cart as $itemId => $item) {
            // Handle both products and variants
            if (isset($item['variant_id'])) {
                $variant = ProductVariant::with('product')->find($item['variant_id']);
                if ($variant) {
                    $product = $variant->product;
                    $price = $product->base_price + $variant->price_adjustment;
                    $cartItems[] = [
                        'id' => $itemId,
                        'product' => $product,
                        'variant' => $variant,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'subtotal' => $price * $item['quantity']
                    ];
                    $total += $price * $item['quantity'];
                }
            } else {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $cartItems[] = [
                        'id' => $itemId,
                        'product' => $product,
                        'variant' => null,
                        'quantity' => $item['quantity'],
                        'price' => $product->base_price,
                        'subtotal' => $product->base_price * $item['quantity']
                    ];
                    $total += $product->base_price * $item['quantity'];
                }
            }
        }
        
        // Get user's franchisee information
        $franchisee = Auth::user()->franchisee;
        
        return view('franchisee.checkout', compact('cartItems', 'total', 'franchisee'));
    }
    
    /**
     * Process the order.
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
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('franchisee.cart')
                ->with('error', 'Your cart is empty. Please add some products before checkout.');
        }
        
        // Calculate total
        $total = 0;
        $items = [];
        
        foreach ($cart as $itemId => $item) {
            if (isset($item['variant_id'])) {
                $variant = ProductVariant::with('product')->find($item['variant_id']);
                if ($variant) {
                    $product = $variant->product;
                    $price = $product->base_price + $variant->price_adjustment;
                    $subtotal = $price * $item['quantity'];
                    $total += $subtotal;
                    
                    $items[] = [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => $item['quantity'],
                        'price' => $price
                    ];
                }
            } else {
                $product = Product::find($item['product_id']);
                if ($product) {
                    $subtotal = $product->base_price * $item['quantity'];
                    $total += $subtotal;
                    
                    $items[] = [
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'quantity' => $item['quantity'],
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
            foreach ($items as $item) {
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
            
            // Commit the transaction
            DB::commit();
            
            // IMPORTANT: Clear the cart after successfully completing the transaction
            Session::forget('cart');
            
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
}