<?php

namespace App\Http\Controllers\Franchisee;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
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
        
        // Check if product is in stock
        $product = Product::find($productId);
        if (!$product || $product->inventory_count < $quantity) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is out of stock or has insufficient inventory.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Product is out of stock or has insufficient inventory.');
        }
        
        // Check variant stock if applicable
        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if (!$variant || $variant->inventory_count < $quantity) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected variant is out of stock or has insufficient inventory.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Selected variant is out of stock or has insufficient inventory.');
            }
        }
        
        // Get cart from session
        $cart = Session::get('cart', []);
        
        // Generate a unique cart item ID
        $itemId = uniqid();
        
        // Add to cart
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
        
        // Save cart back to session
        Session::put('cart', $cart);
        
        // Return success response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'cart_count' => count($cart)
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
     * Remove an item from the cart.
     */
    public function removeFromCart(Request $request)
    {
        $itemId = $request->input('item_id');
        $cart = Session::get('cart', []);
        
        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::put('cart', $cart);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart.',
                    'cart_count' => count($cart)
                ]);
            }
            
            return redirect()->back()->with('success', 'Item removed from cart.');
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
        $order->contact_phone = $request->input('contact_phone', '1234567890');
        $order->payment_method = $request->input('payment_method', 'account');
        
        // Save the order
        $order->save();
        
        // Create order items
        foreach ($items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ]);
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