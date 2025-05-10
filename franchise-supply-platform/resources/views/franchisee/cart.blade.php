@extends('layouts.franchisee')

@section('title', 'Shopping Cart - Restaurant Supply Platform')

@section('page-title', 'Shopping Cart')

@section('styles')
<style>
    /* Page title styling */
    .page-title-container {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    
    .page-title {
        font-size: 2rem;
        font-weight: 600;
        margin: 0;
    }
    
    /* Welcome section */
    .welcome-container {
        background-color: #f1f9f1;
        border-left: 4px solid #28a745;
        border-radius: 5px;
        padding: 15px 20px;
        margin-bottom: 20px;
    }
    
    .welcome-title {
        display: flex;
        align-items: center;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .welcome-star {
        color: #28a745;
        margin-right: 10px;
    }
    
    /* Cart styling */
    .cart-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }
    
    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .cart-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0;
    }
    
    .cart-item {
        display: flex;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .cart-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        margin-right: 20px;
    }
    
    .cart-item-details {
        flex-grow: 1;
    }
    
    .cart-item-name {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .cart-item-price {
        color: #28a745;
        font-weight: 500;
    }
    
    .cart-quantity {
        display: flex;
        align-items: center;
        margin: 0 30px;
    }
    
    .quantity-btn {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        font-size: 16px;
        cursor: pointer;
    }
    
    .quantity-btn:hover {
        background-color: #e9ecef;
    }
    
    .quantity-btn.minus {
        border-radius: 4px 0 0 4px;
    }
    
    .quantity-btn.plus {
        border-radius: 0 4px 4px 0;
    }
    
    .quantity-input {
        width: 50px;
        height: 34px;
        border: 1px solid #ced4da;
        border-left: none;
        border-right: none;
        text-align: center;
        font-size: 14px;
        padding: 0;
        -moz-appearance: textfield;
    }
    
    .quantity-input::-webkit-outer-spin-button,
    .quantity-input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    .cart-item-total {
        font-weight: 600;
        font-size: 1.1rem;
        width: 80px;
        text-align: right;
        margin-right: 20px;
    }
    
    .btn-remove {
        background-color: #fff;
        color: #dc3545;
        border: 1px solid #dc3545;
        font-size: 14px;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .btn-remove:hover {
        background-color: #f8d7da;
    }
    
    .cart-actions {
        display: flex;
        justify-content: flex-start;
        padding: 15px 20px;
        gap: 10px;
    }
    
    .clear-cart-btn {
        background-color: #fff;
        color: #dc3545;
        border: 1px solid #dc3545;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .clear-cart-btn:hover {
        background-color: #f8d7da;
    }
    
    /* Order summary */
    .order-summary {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }
    
    .order-summary-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
        color: #fff;
        background-color: #4CAF50;
        margin: -20px -20px 20px -20px;
        padding: 15px 20px;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }
    
    .order-summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    
    .order-summary-total {
        display: flex;
        justify-content: space-between;
        font-weight: 600;
        font-size: 1.1rem;
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px solid #dee2e6;
    }
    
    .checkout-btn {
        display: block;
        width: 100%;
        background-color: #28a745;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 4px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        margin-top: 20px;
        text-align: center;
    }
    
    .checkout-btn:hover {
        background-color: #218838;
    }
    
    .checkout-btn:disabled {
        background-color: #6c757d;
        cursor: not-allowed;
    }
    
    /* Quick links */
    .quick-links {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
    }
    
    .quick-links-title {
        font-size: 1.1rem;
        font-weight: 600;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .quick-links-content {
        padding: 15px 20px;
    }
    
    .quick-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
        border-radius: 4px;
        margin-bottom: 10px;
        text-decoration: none;
        color: #495057;
        background-color: #f8f9fa;
        transition: all 0.2s;
        text-align: center;
    }
    
    .quick-link:hover {
        background-color: #e9ecef;
    }
    
    .quick-link i {
        margin-right: 8px;
    }
    
    /* Empty cart */
    .empty-cart {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 50px 20px;
        text-align: center;
    }
    
    .empty-cart-icon {
        font-size: 3rem;
        color: #dee2e6;
        margin-bottom: 20px;
    }
    
    .empty-cart-title {
        font-size: 1.5rem;
        font-weight: 500;
        margin-bottom: 10px;
        color: #6c757d;
    }
    
    .empty-cart-message {
        color: #6c757d;
        margin-bottom: 20px;
    }
    
    .shop-now-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .shop-now-btn:hover {
        background-color: #218838;
    }
    
    /* Continue shopping link */
    .continue-shopping {
        display: inline-flex;
        align-items: center;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
    }
    
    .continue-shopping i {
        margin-right: 5px;
    }
    
    .continue-shopping:hover {
        text-decoration: underline;
    }

    /* Success message toast */
    .toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
    }
    
    .toast {
        transition: opacity 0.3s ease;
        opacity: 0;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        max-width: 350px;
    }
    
    .toast.show {
        opacity: 1;
    }
    
    .toast.success {
        border-left: 4px solid #28a745;
    }
    
    .toast.error {
        border-left: 4px solid #dc3545;
    }
    
    .toast-icon {
        margin-right: 10px;
        font-size: 1.2rem;
    }
    
    .toast.success .toast-icon {
        color: #28a745;
    }
    
    .toast.error .toast-icon {
        color: #dc3545;
    }
    
    .toast-content {
        flex-grow: 1;
    }
    
    .toast-close {
        background: none;
        border: none;
        font-size: 1.1rem;
        color: #6c757d;
        cursor: pointer;
    }

    /* Quantity adjustment highlight effect */
    .highlight {
        animation: highlight 1s ease-in-out;
    }

    @keyframes highlight {
        0% { background-color: transparent; }
        50% { background-color: rgba(40, 167, 69, 0.2); }
        100% { background-color: transparent; }
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }

    .loading-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #e9ecef;
        border-top: 5px solid #28a745;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8 mb-4">
            <div class="cart-container">
                <div class="cart-header">
                    <h2 class="cart-title">Shopping Cart</h2>
                    <a href="{{ route('franchisee.catalog') }}" class="continue-shopping">
                        <i class="fas fa-arrow-left"></i> Continue Shopping
                    </a>
                </div>
                
                @if(count($cartItems) > 0)
                    <div id="cart-items-container">
                        @foreach($cartItems as $item)
                            <div class="cart-item" data-item-id="{{ $item['id'] }}">
                                <img src="{{ asset('storage/' . ($item['product']->images->first()->image_url ?? 'default-product.jpg')) }}" 
                                     alt="{{ $item['product']->name }}" class="cart-image">
                                
                                <div class="cart-item-details">
                                    <div class="cart-item-name">{{ $item['product']->name }}</div>
                                    @if($item['variant'])
                                        <div class="cart-item-variant">{{ $item['variant']->name }}</div>
                                    @endif
                                    <div class="cart-item-price" data-price="{{ $item['price'] }}">${{ number_format($item['price'], 2) }}</div>
                                </div>
                                
                                <div class="cart-quantity">
                                    <button type="button" class="quantity-btn minus" data-item-id="{{ $item['id'] }}">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" value="{{ $item['quantity'] }}" 
                                           min="1" class="quantity-input" 
                                           data-item-id="{{ $item['id'] }}"
                                           data-initial-quantity="{{ $item['quantity'] }}">
                                    <button type="button" class="quantity-btn plus" data-item-id="{{ $item['id'] }}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="cart-item-total" data-item-id="{{ $item['id'] }}">${{ number_format($item['subtotal'], 2) }}</div>
                                
                                <button type="button" class="btn-remove" data-item-id="{{ $item['id'] }}">
                                    <i class="fas fa-trash"></i> Remove All
                                </button>
                            </div>
                        @endforeach
                        
                        <div class="cart-actions">
                            <button type="button" id="clear-cart-btn" class="clear-cart-btn">
                                <i class="fas fa-trash-alt"></i> Clear Cart
                            </button>
                        </div>
                    </div>
                @else
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3 class="empty-cart-title">Your cart is empty</h3>
                        <p class="empty-cart-message">Looks like you haven't added any products to your cart yet.</p>
                        <a href="{{ route('franchisee.catalog') }}" class="shop-now-btn">
                            <i class="fas fa-shopping-bag me-2"></i> Browse Products
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="order-summary">
                <h3 class="order-summary-title">Order Summary</h3>
                
                <div class="order-summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">${{ number_format($total ?? 0, 2) }}</span>
                </div>
                
                <div class="order-summary-row">
                    <span>Shipping:</span>
                    <span id="shipping">$0.00</span>
                </div>
                
                <div class="order-summary-total">
                    <span>Total:</span>
                    <span id="total">${{ number_format($total ?? 0, 2) }}</span>
                </div>
                
                @if(count($cartItems ?? []) > 0)
                    <a href="{{ route('franchisee.cart.checkout') }}" class="checkout-btn">
                        <i class="fas fa-check-circle me-2"></i> Proceed to Checkout
                    </a>
                @else
                    <button class="checkout-btn" disabled>
                        <i class="fas fa-check-circle me-2"></i> Proceed to Checkout
                    </button>
                @endif
            </div>
            
            <!-- Quick Links -->
            <div class="quick-links">
                <div class="quick-links-title">Quick Links</div>
                <div class="quick-links-content">
                    <a href="{{ route('franchisee.orders.pending') }}" class="quick-link">
                        <i class="fas fa-shipping-fast"></i> Track Orders
                    </a>
                    <a href="{{ route('franchisee.orders.history') }}" class="quick-link">
                        <i class="fas fa-history"></i> Order History
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast notification container -->
<div class="toast-container"></div>

<!-- Loading overlay -->
<div class="loading-overlay">
    <div class="spinner"></div>
</div>
@endsection

@section('scripts')
@include('layouts.components.alert-component')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initial calculation of cart totals
        calculateCartTotals();
        
        // Quantity buttons functionality with automatic update
        setupQuantityButtons();
        
        // Remove item functionality
        setupRemoveButtons();
        
        // Clear cart functionality
        setupClearCartButton();
        
        // Debounce function to limit API calls
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        
        // Function to show loading overlay
        function showLoading() {
            const loadingOverlay = document.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('active');
            }
        }
        
        // Function to hide loading overlay
        function hideLoading() {
            const loadingOverlay = document.querySelector('.loading-overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.remove('active');
            }
        }
        
        // Function to calculate cart totals based on displayed items
        function calculateCartTotals() {
            let subtotal = 0;
            
            // Get all cart items
            const cartItems = document.querySelectorAll('.cart-item');
            
            // Loop through each item and add its subtotal
            cartItems.forEach(item => {
                const itemTotal = item.querySelector('.cart-item-total').textContent;
                const itemValue = parseFloat(itemTotal.replace('$', '').replace(',', ''));
                subtotal += itemValue;
            });
            
            // Update the subtotal and total in the order summary
            const subtotalElement = document.getElementById('subtotal');
            const totalElement = document.getElementById('total');
            
            if (subtotalElement) {
                subtotalElement.textContent = '$' + subtotal.toFixed(2);
            }
            
            if (totalElement) {
                totalElement.textContent = '$' + subtotal.toFixed(2);
            }
            
            return subtotal;
        }
        
        // Function to calculate item subtotal
        function calculateItemSubtotal(itemElement) {
            const priceElement = itemElement.querySelector('.cart-item-price');
            const quantityInput = itemElement.querySelector('.quantity-input');
            const subtotalElement = itemElement.querySelector('.cart-item-total');
            
            if (!priceElement || !quantityInput || !subtotalElement) return;
            
            const price = parseFloat(priceElement.dataset.price);
            const quantity = parseInt(quantityInput.value);
            const subtotal = price * quantity;
            
            subtotalElement.textContent = '$' + subtotal.toFixed(2);
            
            // Highlight the changed total
            subtotalElement.classList.add('highlight');
            setTimeout(() => {
                subtotalElement.classList.remove('highlight');
            }, 1000);
            
            return subtotal;
        }
        
        // Function to update all cart badges across the interface
        function updateAllCartBadges(count, totalItems) {
            // Use the global function if available (defined in layout)
            if (window.updateAllCartCountBadges) {
                // Pass totalItems if available, otherwise just use count
                window.updateAllCartCountBadges(totalItems || count);
            } else {
                // Fallback if global function isn't available
                // Update top navigation cart badge
                const topNavBadge = document.querySelector('#top-cart-btn .badge');
                if (topNavBadge) {
                    if (count > 0) {
                        topNavBadge.textContent = totalItems || count;
                        topNavBadge.style.display = '';
                    } else {
                        topNavBadge.style.display = 'none';
                    }
                } else if (count > 0) {
                    const cartBtn = document.querySelector('#top-cart-btn');
                    if (cartBtn) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                        newBadge.textContent = totalItems || count;
                        cartBtn.appendChild(newBadge);
                    }
                }
                
                // Update sidebar cart badge
                const sidebarBadge = document.querySelector('#sidebar-cart-link .badge');
                if (sidebarBadge) {
                    if (count > 0) {
                        sidebarBadge.textContent = totalItems || count;
                        sidebarBadge.style.display = '';
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                } else if (count > 0) {
                    const sidebarLink = document.querySelector('#sidebar-cart-link');
                    if (sidebarLink) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger ms-2 cart-sidebar-count';
                        newBadge.textContent = totalItems || count;
                        sidebarLink.appendChild(newBadge);
                    }
                }
            }
            
            // Dispatch a custom event for other components to listen for
            document.dispatchEvent(new CustomEvent('cartUpdated', { 
                detail: { count: count, totalItems: totalItems || count }
            }));
        }
        
        // Function to update cart quantity via AJAX
        const updateCartItemQuantity = debounce(function(itemId, quantity, previousQuantity) {
            // Show loading indicator
            showLoading();
            
            // Determine if this is a partial removal
            const isPartialRemoval = previousQuantity && quantity < previousQuantity;
            
            // If quantity is zero, handle as a removal
            if (quantity === 0) {
                removeCartItem(itemId);
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('items[0][id]', itemId);
            formData.append('items[0][quantity]', quantity);
            
            // Make AJAX request
            fetch('{{ route("franchisee.cart.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success toast using the global function
                    // Use correct parameter order: (message, type)
                    if (typeof window.showFloatingAlert === 'function') {
                        window.showFloatingAlert(isPartialRemoval ? 'Item quantity reduced' : 'Cart updated successfully', 'success');
                    }
                    
                    // Update cart count everywhere
                    updateAllCartBadges(data.cart_count, data.total_items);
                } else {
                    // Show error toast using the global function
                    // Use correct parameter order and 'danger' instead of 'error'
                    if (typeof window.showFloatingAlert === 'function') {
                        window.showFloatingAlert(data.message || 'Failed to update cart', 'danger');
                    }
                    
                    // Revert to previous quantity
                    revertQuantityChange(itemId, previousQuantity);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Use global function with correct parameter order
                if (typeof window.showFloatingAlert === 'function') {
                    window.showFloatingAlert('An error occurred while updating the cart', 'danger');
                }
                revertQuantityChange(itemId, previousQuantity);
            })
            .finally(() => {
                // Hide loading indicator
                hideLoading();
            });
        }, 500); // 500ms debounce delay
        
        // Function to revert quantity change if API call fails
        function revertQuantityChange(itemId, previousQuantity) {
            const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
            if (input) {
                // Reset to initial quantity (or previous quantity if provided)
                input.value = previousQuantity || input.dataset.initialQuantity || 1;
                
                // Recalculate subtotal
                const itemElement = input.closest('.cart-item');
                calculateItemSubtotal(itemElement);
                
                // Recalculate cart totals
                calculateCartTotals();
            }
        }
        
        // Set up quantity buttons with auto-update
        function setupQuantityButtons() {
            const minusButtons = document.querySelectorAll('.quantity-btn.minus');
            const plusButtons = document.querySelectorAll('.quantity-btn.plus');
            const quantityInputs = document.querySelectorAll('.quantity-input');
            
            minusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.dataset.itemId;
                    const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    const currentValue = parseInt(input.value);
                    
                    if (currentValue > 1) {
                        // Store previous value before updating
                        const previousValue = currentValue;
                        
                        // Decrease value
                        input.value = currentValue - 1;
                        
                        // Update subtotal for this item
                        const itemElement = this.closest('.cart-item');
                        calculateItemSubtotal(itemElement);
                        
                        // Update cart totals
                        calculateCartTotals();
                        
                        // Send update to server (with previous value for comparison)
                        updateCartItemQuantity(itemId, currentValue - 1, previousValue);
                    } else if (currentValue === 1) {
                        // If quantity is already 1, ask if they want to remove the item
                        if (confirm('Remove this item from your cart?')) {
                            removeCartItem(itemId);
                        }
                    }
                });
            });
            
            plusButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.dataset.itemId;
                    const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    const currentValue = parseInt(input.value);
                    
                    // Store previous value before updating
                    const previousValue = currentValue;
                    
                    // Increase value
                    input.value = currentValue + 1;
                    
                    // Update subtotal for this item
                    const itemElement = this.closest('.cart-item');
                    calculateItemSubtotal(itemElement);
                    
                    // Update cart totals
                    calculateCartTotals();
                    
                    // Send update to server (with previous value for comparison)
                    updateCartItemQuantity(itemId, currentValue + 1, previousValue);
                });
            });
            
            quantityInputs.forEach(input => {
                // Store initial value for reverting if needed
                input.dataset.initialQuantity = input.value;
                
                // Add change event listener
                input.addEventListener('change', function() {
                    const itemId = this.dataset.itemId;
                    const previousValue = parseInt(this.dataset.initialQuantity) || 1;
                    let newValue = parseInt(this.value);
                    
                    // Ensure minimum value of 1
                    if (newValue < 1) {
                        // If they entered 0, ask if they want to remove the item
                        if (newValue === 0) {
                            if (confirm('Remove this item from your cart?')) {
                                removeCartItem(itemId);
                                return;
                            } else {
                                // If they cancel, revert to previous value
                                newValue = previousValue;
                                this.value = previousValue;
                            }
                        } else {
                            // Invalid negative value, set to 1
                            newValue = 1;
                            this.value = 1;
                        }
                    }
                    
                    // Update subtotal for this item
                    const itemElement = this.closest('.cart-item');
                    calculateItemSubtotal(itemElement);
                    
                    // Update cart totals
                    calculateCartTotals();
                    
                    // Update current value as initial value
                    this.dataset.initialQuantity = newValue;
                    
                    // Send update to server (with previous value for comparison)
                    updateCartItemQuantity(itemId, newValue, previousValue);
                });
            });
        }
        
        // Function to remove cart item
        function removeCartItem(itemId, quantity = null) {
            const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
            if (!cartItem) return;
            
            // Show loading indicator
            showLoading();
            
            // Create form data
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('item_id', itemId);
            
            // Add quantity if specified (for partial removal)
            if (quantity !== null) {
                formData.append('quantity', quantity);
            }
            
            // Make AJAX request
            fetch('{{ route("franchisee.cart.remove") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (quantity === null || !data.cart_count || quantity >= parseInt(cartItem.querySelector('.quantity-input').value)) {
                        // Full removal - fade out the item
                        cartItem.style.opacity = '0';
                        cartItem.style.transition = 'opacity 0.3s ease';
                        
                        setTimeout(() => {
                            // Remove the item from DOM
                            cartItem.remove();
                            
                            // Show success toast using the global function
                            if (typeof window.showFloatingAlert === 'function') {
                                window.showFloatingAlert(data.message || 'Item removed from cart', 'success');
                            }
                            
                            // Update cart count everywhere
                            updateAllCartBadges(data.cart_count, data.total_items);
                            
                            // Recalculate cart totals
                            calculateCartTotals();
                            
                            // If cart is now empty, reload the page
                            if (data.cart_count === 0) {
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            }
                        }, 300);
                    } else {
                        // Partial removal - update the quantity
                        const quantityInput = cartItem.querySelector('.quantity-input');
                        if (quantityInput) {
                            // Get the new quantity from the server response if available
                            const newQuantity = data.item_quantity || (parseInt(quantityInput.value) - quantity);
                            quantityInput.value = newQuantity;
                            quantityInput.dataset.initialQuantity = newQuantity;
                            
                            // Recalculate subtotal
                            calculateItemSubtotal(cartItem);
                            calculateCartTotals();
                            
                            // Show success toast
                            if (typeof window.showFloatingAlert === 'function') {
                                window.showFloatingAlert(data.message || 'Item quantity reduced', 'success');
                            }
                            
                            // Update cart count
                            updateAllCartBadges(data.cart_count, data.total_items);
                        }
                    }
                } else {
                    // Show error toast using the global function
                    if (typeof window.showFloatingAlert === 'function') {
                        window.showFloatingAlert(data.message || 'Failed to remove item', 'danger');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof window.showFloatingAlert === 'function') {
                    window.showFloatingAlert('An error occurred while removing the item', 'danger');
                }
            })
            .finally(() => {
                // Hide loading indicator
                hideLoading();
            });
        }
        
        // Set up remove buttons with AJAX functionality
        function setupRemoveButtons() {
            const removeButtons = document.querySelectorAll('.btn-remove');
            
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (!confirm('Are you sure you want to remove this item?')) return;
                    
                    const itemId = this.dataset.itemId;
                    removeCartItem(itemId);
                });
            });
        }
        
        // Set up clear cart button
        function setupClearCartButton() {
            const clearCartBtn = document.getElementById('clear-cart-btn');
            
            if (clearCartBtn) {
                clearCartBtn.addEventListener('click', function() {
                    if (!confirm('Are you sure you want to clear your cart?')) return;
                    
                    // Show loading indicator
                    showLoading();
                    
                    // Make AJAX request
                    fetch('{{ route("franchisee.cart.clear") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update all cart badges to zero before reload
                            updateAllCartBadges(0, 0);
                            
                            // Show success message using the global function
                            if (typeof window.showFloatingAlert === 'function') {
                                window.showFloatingAlert('Cart has been cleared', 'success');
                            }
                            
                            // Reload the page after a short delay
                            setTimeout(() => {
                                window.location.reload();
                            }, 1500);
                        } else {
                            // Show error toast using the global function
                            if (typeof window.showFloatingAlert === 'function') {
                                window.showFloatingAlert(data.message || 'Failed to clear cart', 'danger');
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof window.showFloatingAlert === 'function') {
                            window.showFloatingAlert('An error occurred while clearing the cart', 'danger');
                        }
                    })
                    .finally(() => {
                        // Hide loading indicator
                        hideLoading();
                    });
                });
            }
        }
    });
</script>
@endsection