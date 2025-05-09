{{-- 
  Add to Cart Component
  This component contains the product modal HTML and all the necessary scripts and styles
  Place this file at: resources/views/layouts/components/add-to-cart.blade.php
--}}

{{-- CSS Styles for modal and related elements --}}
<style>
/* Styling for product thumbnails */
.thumbnail-wrapper {
    width: 75px !important;
    height: 75px !important;
    overflow: hidden;
    border-radius: 4px;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    position: relative;
    cursor: pointer;
    margin: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.thumbnail-wrapper:hover {
    transform: scale(1.05);
    border-color: #28a745;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.thumbnail-wrapper.active-thumb {
    border-color: #4e73df;
    box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.5);
}

.product-thumbnail, .variant-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.thumbnails-container {
    padding: 5px;
    background-color: #f8f9fa;
    border-radius: 5px;
    overflow-x: auto;
    min-height: 85px;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Variant label for thumbnails */
.variant-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.6);
    color: white;
    font-size: 10px;
    padding: 2px 4px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    opacity: 0.9;
    transition: all 0.2s ease;
}

.variant-thumb:hover .variant-label {
    opacity: 1;
    background: rgba(0,0,0,0.8);
}

/* Fade effect for content changes */
.main-display-content {
    transition: opacity 0.3s ease;
}

.fade-content {
    opacity: 0;
}

/* Custom quantity input styling */
.quantity-input {
    /* Hide the default up/down arrows */
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Amazon-style add to cart section */
.add-to-cart-section {
    margin-top: 1rem;
}

.add-to-cart-btn {
    padding: 0.75rem;
    font-size: 1.1rem;
}

/* Styling for the main product image */
.main-product-image {
    border: 1px solid #e3e6f0;
    border-radius: 4px;
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #fff;
}

.main-product-image img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

/* Variants table styling */
.table-bordered {
    border-color: #e3e6f0;
}

.table-hover tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.table-light th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom-width: 1px;
}

/* Status badges */
.badge.bg-success, 
.badge.bg-warning, 
.badge.bg-danger,
.badge.bg-secondary {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

/* Improve modal layout */
.modal-lg {
    max-width: 900px;
}

.modal-body {
    padding: 1.5rem;
}

.modal-header {
    border-bottom: 1px solid #e3e6f0;
    background-color: #f8f9fa;
}

.modal-footer {
    border-top: 1px solid #e3e6f0;
    background-color: #f8f9fa;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .thumbnails-container {
        justify-content: flex-start !important;
    }
    
    .thumbnail-wrapper {
        width: 65px !important;
        height: 65px !important;
    }
    
    .modal-body .row {
        flex-direction: column;
    }
    
    .modal-body .col-md-5,
    .modal-body .col-md-7 {
        width: 100%;
    }
    
    .modal-body .col-md-5 {
        margin-bottom: 1rem;
    }
    
    .main-product-image {
        height: 250px !important;
    }
}
</style>

{{-- Generate modals for all products --}}
@foreach($products as $product)
<!-- Product Modal with Amazon-Style UI and Fixed Thumbnails -->
<div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1" aria-labelledby="productModalLabel{{ $product->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel{{ $product->id }}">
                    <span class="main-product-name">{{ $product->name }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Main Product/Variant Display Section -->
                <div class="row">
                    <div class="col-md-5 position-relative">
                        <div class="main-product-image" style="height: 300px;">
                            @if($product->images && $product->images->count() > 0)
                                <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                    class="img-fluid rounded w-100 h-100" style="object-fit: contain;" 
                                    alt="{{ $product->name }}" id="mainDisplayImage{{ $product->id }}">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Thumbnails Row -->
                        <div class="product-thumbnails mt-3">
                            <div class="d-flex flex-wrap justify-content-center gap-2 thumbnails-container">
                                <!-- Product Thumbnails -->
                                @if($product->images && $product->images->count() > 0)
                                    @foreach($product->images as $index => $image)
                                        <div class="thumbnail-wrapper product-thumb {{ $index === 0 ? 'active-thumb' : '' }}" 
                                             data-product-id="{{ $product->id }}" 
                                             data-image-url="{{ asset('storage/' . $image->image_url) }}">
                                            <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                 alt="{{ $product->name }} thumbnail {{ $index + 1 }}"
                                                 class="product-thumbnail">
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <!-- Dynamic content that changes based on selection -->
                        <div class="main-display-content">
                            <h4 class="main-display-name">{{ $product->name }}</h4>
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-secondary me-2">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                <span class="text-muted"><i class="fas fa-building me-1"></i> {{ $product->supplier->name ?? 'Unknown Supplier' }}</span>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3 main-display-price">
                                <h3 class="me-2 current-price">${{ number_format($product->price, 2) }}</h3>
                                @if(isset($product->compare_price) && $product->compare_price > $product->price)
                                    <span class="text-muted text-decoration-line-through">${{ number_format($product->compare_price, 2) }}</span>
                                    <span class="badge bg-danger ms-2">{{ round((1 - $product->price / $product->compare_price) * 100) }}% OFF</span>
                                @endif
                            </div>
                            
                            <div class="mb-3 main-display-stock">
                                @if($product->inventory_count > 10)
                                    <span class="badge bg-success">In Stock</span>
                                @elseif($product->inventory_count > 0)
                                    <span class="badge bg-warning text-dark">Low Stock</span>
                                @else
                                    <span class="badge bg-danger">Out of Stock</span>
                                @endif
                                
                                <span class="ms-2 inventory-count">{{ $product->inventory_count ?? 0 }} left</span>
                            </div>
                            
                            <div class="mb-4">
                                <p class="main-display-description">{{ $product->description }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <p class="mb-1"><strong>Product Details:</strong></p>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-weight me-2"></i> <span class="unit-details">{{ $product->unit_size ?? '' }} {{ $product->unit_type ?? '' }}</span></li>
                                    <li><i class="fas fa-box me-2"></i> SKU: <span class="sku-details">{{ $product->sku ?? 'N/A' }}</span></li>
                                    @if(isset($product->min_order_quantity) && $product->min_order_quantity > 0)
                                        <li><i class="fas fa-truck-loading me-2"></i> Min Order: <span class="min-order">{{ $product->min_order_quantity }}</span></li>
                                    @endif
                                </ul>
                            </div>
                            
                            <!-- Add to Cart Form - Simplified and Amazon-like -->
                            <div class="add-to-cart-section border-top pt-3">
                                <form action="{{ route('franchisee.cart.add') }}" method="POST" class="d-inline add-to-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="variant_id" value="" class="selected-variant-id">
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <label class="me-3">Quantity:</label>
                                        <div class="input-group" style="width: 130px;">
                                            <button type="button" class="btn btn-outline-secondary quantity-decrement">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" name="quantity" value="1" min="1" max="{{ $product->inventory_count }}" 
                                                   class="form-control text-center quantity-input" 
                                                   style="width: 50px;"
                                                   inputmode="numeric">
                                            <button type="button" class="btn btn-outline-secondary quantity-increment">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-success btn-lg add-to-cart-btn" {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                            <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                        </button>
                                        
                                        <button type="button" class="btn btn-outline-secondary product-favorite" data-product-id="{{ $product->id }}">
                                            <i class="fas fa-heart {{ $product->is_favorite > 0 ? 'text-danger' : '' }}"></i> 
                                            {{ $product->is_favorite > 0 ? 'Remove from Favorites' : 'Add to Favorites' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Will be shown when viewing a variant -->
                            <div class="variant-specific-details mt-3" style="display: none;">
                                <div class="alert alert-light border">
                                    <div class="d-flex">
                                        <button class="btn btn-outline-secondary btn-sm me-2 return-to-main-product" data-product-id="{{ $product->id }}">
                                            <i class="fas fa-arrow-left"></i> Back to main product
                                        </button>
                                        <div>
                                            <p class="mb-0">Currently viewing:</p>
                                            <p class="mb-0 fw-bold current-variant-name"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Variants Section -->
                @if($product->variants && $product->variants->count() > 0)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 mb-3">
                            <span class="variants-section-title">Choose a Variant</span>
                        </h5>
                        
                        <!-- Variants Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;">Image</th>
                                        <th>Variant</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th style="width: 100px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="variants-container">
                                    <!-- Original Product Row (Hidden by default, shown when viewing a variant) -->
                                    <tr class="main-product-row" style="display: none;" data-item-id="main-{{ $product->id }}">
                                        <td class="text-center">
                                            @if($product->images && $product->images->count() > 0)
                                                <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                                    class="img-thumbnail product-thumbnail" 
                                                    style="height: 50px; width: 50px; object-fit: cover;"
                                                    alt="{{ $product->name }}">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold">{{ $product->name }}</span>
                                            <div class="badge bg-secondary">Original Product</div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">${{ number_format($product->price, 2) }}</div>
                                        </td>
                                        <td>
                                            @if($product->inventory_count > 10)
                                                <span class="status-in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                            @elseif($product->inventory_count > 0)
                                                <span class="status-low-stock"><i class="fas fa-exclamation-circle me-1"></i> Low Stock</span>
                                            @else
                                                <span class="status-out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary w-100 select-item" 
                                                    data-item-id="main-{{ $product->id }}"
                                                    data-name="{{ $product->name }}"
                                                    data-description="{{ $product->description }}"
                                                    data-price="{{ $product->price }}"
                                                    data-inventory="{{ $product->inventory_count }}"
                                                    data-image-url="{{ $product->images && $product->images->count() > 0 ? asset('storage/' . $product->images->first()->image_url) : '' }}"
                                                    data-is-main="true">
                                                <i class="fas fa-exchange-alt me-1"></i> Select
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Variant Rows -->
                                    @foreach($product->variants as $variant)
                                    <tr class="variant-row" data-item-id="variant-{{ $variant->id }}">
                                        <td class="text-center">
                                            @if($variant->images && $variant->images->count() > 0)
                                                <img src="{{ asset('storage/' . $variant->images->first()->image_url) }}" 
                                                    class="img-thumbnail variant-thumbnail" 
                                                    style="height: 50px; width: 50px; object-fit: cover;"
                                                    alt="{{ $variant->name }}">
                                            @elseif($variant->image_url)
                                                <img src="{{ asset('storage/' . $variant->image_url) }}" 
                                                    class="img-thumbnail variant-thumbnail" 
                                                    style="height: 50px; width: 50px; object-fit: cover;"
                                                    alt="{{ $variant->name }}">
                                            @else
                                                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $variant->name }}</td>
                                        <td>
                                            <div class="fw-bold">${{ number_format($variant->price_adjustment, 2) }}</div>
                                        </td>
                                        <td>
                                            @if($variant->inventory_count > 10)
                                                <span class="status-in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                            @elseif($variant->inventory_count > 0)
                                                <span class="status-low-stock"><i class="fas fa-exclamation-circle me-1"></i> Low Stock</span>
                                            @else
                                                <span class="status-out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-primary w-100 select-item" 
                                                    data-item-id="variant-{{ $variant->id }}"
                                                    data-name="{{ $variant->name }}"
                                                    data-description="{{ $variant->description ?? $product->description }}"
                                                    data-price="{{$variant->price_adjustment }}"
                                                    data-inventory="{{ $variant->inventory_count }}"
                                                    data-image-url="{{ $variant->images && $variant->images->count() > 0 ? asset('storage/' . $variant->images->first()->image_url) : ($variant->image_url ? asset('storage/' . $variant->image_url) : '') }}"
                                                    data-variant-id="{{ $variant->id }}"
                                                    data-has-images="{{ ($variant->images && $variant->images->count() > 0) || $variant->image_url ? 'true' : 'false' }}"
                                                    @if($variant->images && $variant->images->count() > 0)
                                                    data-variant-images="{{ json_encode($variant->images->map(function($img) { 
                                                        return [
                                                            'id' => $img->id, 
                                                            'image_url' => asset('storage/' . $img->image_url)
                                                        ]; 
                                                    })) }}"
                                                    @endif
                                                    >
                                                <i class="fas fa-exchange-alt me-1"></i> Select
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endforeach

{{-- JavaScript functionality --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Process all product modals on the page for variant swapping
    document.querySelectorAll('[id^="productModal"]').forEach(modal => {
        const productId = modal.id.replace('productModal', '');
        setupVariantSwapping(modal, productId);
        
        // ADD THIS: Fix for close button
        const closeBtn = modal.querySelector('.btn-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function(e) {
                // Prevent default behavior
                e.preventDefault();
                
                // Get the Bootstrap modal instance 
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    // Use Bootstrap's method to hide the modal
                    bsModal.hide();
                }
                
                // Force cleanup
                setTimeout(function() {
                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }, 300);
            });
        }
    });
    
    // Initialize quick add to cart buttons to open the modal
    document.querySelectorAll('.quick-add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            if (!productId) return;
            
            // Find the corresponding product modal
            const modal = document.getElementById('productModal' + productId);
            if (!modal) return;
            
            // Open the modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();
            
            // After modal is shown, scroll to the add to cart section
            modal.addEventListener('shown.bs.modal', function scrollToAddToCart() {
                // Find the add to cart section
                const addToCartSection = modal.querySelector('.add-to-cart-section');
                if (addToCartSection) {
                    // Scroll into view with smooth behavior
                    addToCartSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Remove this event listener to prevent it from firing again
                modal.removeEventListener('shown.bs.modal', scrollToAddToCart);
            });
        });
    });
    
// Process all add to cart forms with AJAX submission
document.querySelectorAll('.add-to-cart-form').forEach(form => {
  form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Get the add to cart button
      const submitBtn = this.querySelector('.add-to-cart-btn');
      if (!submitBtn) return;
      
      // Store original button text and disable
      const originalBtnText = submitBtn.innerHTML;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
      
      // Log the form submission
      console.log("Submitting add to cart form", this.action);
      
      // Get modal if we're in one (defined outside then/catch blocks to be available in both)
      const modal = submitBtn.closest('.modal');
      
      // Submit via AJAX
      fetch(this.action, {
          method: 'POST',
          body: new FormData(this),
          headers: {
              'X-Requested-With': 'XMLHttpRequest'
          }
      })
      .then(response => {
          console.log("Got response from server", response);
          return response.json();
      })
      .then(data => {
        if (data.success) {
            // Get current quantity and product ID for reference
            const quantityInput = this.querySelector('input[name="quantity"]');
            const productId = this.querySelector('input[name="product_id"]').value;
            const currentQuantity = parseInt(quantityInput.value) || 1;
            
            // Show success message
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Added!';
            
            // Get remaining inventory and current cart quantity for this product
            const remainingInventory = data.remaining_inventory || 
                (modal && parseInt(modal.querySelector('.inventory-count').textContent) - currentQuantity);
            const currentCartQuantity = data.product_cart_quantity || 0;
            
            // Create a detailed success message showing what's in cart and what's remaining
            let successMessage = `${currentQuantity} item(s) added to cart`;
            
            // Add cart quantity info if this product is already in cart
            if (currentCartQuantity > currentQuantity) {
                successMessage += ` (${currentCartQuantity} total of this item in cart)`;
            }
            
            // Add inventory info if available - FIX: Don't show negative values in the message
            if (remainingInventory !== undefined) {
                // Display 0 instead of negative values
                const displayInventory = remainingInventory < 0 ? 0 : remainingInventory;
                successMessage += ` (${displayInventory} remaining in stock)`;
            }
            
            // Show the informative notification
            if (typeof showFloatingAlert === 'function') {
                showFloatingAlert(successMessage, 'success');
            }
            
            // Update the displayed inventory count immediately - FIX: Handle negative values for display
            if (modal) {
                const inventoryCountElem = modal.querySelector('.inventory-count');
                if (inventoryCountElem && remainingInventory !== undefined) {
                    // FIX: Display 0 instead of negative values
                    const displayInventory = remainingInventory < 0 ? 0 : remainingInventory;
                    inventoryCountElem.textContent = `${displayInventory} left`;
                }
            }
            
            // Update all product inventory displays on the page with the same product ID
            document.querySelectorAll(`.product-inventory[data-product-id="${productId}"]`).forEach(elem => {
                if (remainingInventory !== undefined) {
                    // FIX: Display 0 instead of negative values
                    const displayInventory = remainingInventory < 0 ? 0 : remainingInventory;
                    elem.textContent = `${displayInventory} left`;
                }
            });
            
            // Update cart counts throughout the site
            if (data.cart_count) {
                // First try using the global function if it exists
                if (typeof window.updateAllCartCountBadges === 'function') {
                    window.updateAllCartCountBadges(data.cart_count);
                } else {
                    // Fall back to dispatching the cartUpdated event
                    document.dispatchEvent(new CustomEvent('cartUpdated', {
                        detail: {
                            count: data.cart_count
                        }
                    }));
                }
                
                // Also update legacy cart count element if it exists
                if (document.getElementById('cart-count')) {
                    document.getElementById('cart-count').textContent = data.cart_count;
                }
            }
            
            // Close modal after success - IMPROVED HANDLING
            setTimeout(() => {
                if (modal) {
                    const modalElement = modal; // Keep a reference to the DOM element
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    if (bsModal) {
                        // Properly hide the modal
                        bsModal.hide();
                        
                        // Force cleanup after slight delay to ensure modal animation completes
                        setTimeout(function() {
                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                            document.body.classList.remove('modal-open');
                            document.body.style.overflow = '';
                            document.body.style.paddingRight = '';
                        }, 300);
                    }
                }
                
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }, 1500);
        } else {
            // Show error
            let errorMessage = data.message || 'Failed to add to cart';
            
            // Get current cart quantity for this product if available
            const currentCartQuantity = data.product_cart_quantity || 0;
            
            // Add more specific info about inventory if available
            if (data.remaining_inventory !== undefined) {
                // If no specific inventory info is provided, try to calculate it from the form/modal
                if (data.remaining_inventory === null && modal) {
                    const inventoryElem = modal.querySelector('.inventory-count');
                    if (inventoryElem) {
                        const inventoryText = inventoryElem.textContent;
                        data.remaining_inventory = parseInt(inventoryText);
                    }
                }
                
                if (!data.requested_quantity) {
                    const quantityInput = form.querySelector('input[name="quantity"]');
                    if (quantityInput) {
                        data.requested_quantity = parseInt(quantityInput.value) || 1;
                    }
                }
                
                // FIX: Handle zero and negative inventory values correctly for error messages
                if (data.remaining_inventory <= 0) {
                    // Zero or negative inventory means it's out of stock or already all in cart
                    if (currentCartQuantity > 0) {
                        // If the user has items in their cart, this means they have all available stock
                        errorMessage = `You already have all available stock (${currentCartQuantity}) in your cart`;
                    } else {
                        // Otherwise it's just out of stock
                        errorMessage = 'This item is out of stock';
                    }
                } else {
                    // We have some inventory available, but not enough for the requested quantity
                    errorMessage = `Adding this quantity would exceed available inventory. Only ${data.remaining_inventory} items available`;
                    
                    if (data.requested_quantity) {
                        errorMessage += ` (you requested ${data.requested_quantity})`;
                    }
                    
                    // Add cart quantity info if this product is already in cart
                    if (currentCartQuantity > 0) {
                        errorMessage += ` - You already have ${currentCartQuantity} in your cart`;
                    }
                }
            }
            
            // Show error message
            if (typeof showFloatingAlert === 'function') {
                showFloatingAlert(errorMessage, 'danger');
            }
            
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
      })
      .catch(error => {
          console.error('Error adding to cart:', error);
          if (typeof showFloatingAlert === 'function') {
              showFloatingAlert('Failed to add to cart. Please try again.', 'danger');
          }
          
          // Reset button state
          submitBtn.disabled = false;
          submitBtn.innerHTML = originalBtnText;
      });
  });
});

// Helper function to update inventory display with fix for negative values
function updateInventoryDisplay(count) {
    const stockDisplay = modal.querySelector('.main-display-stock');
    const inventoryCountElem = modal.querySelector('.inventory-count');
    
    if (!stockDisplay || !inventoryCountElem) return;
    
    // Clear existing status classes and icons
    const statusSpan = stockDisplay.querySelector('span:first-child');
    if (!statusSpan) return;
    
    statusSpan.className = '';
    
    // FIX: Ensure count is never displayed as negative
    const displayCount = count < 0 ? 0 : count;
    
    // Update inventory count
    inventoryCountElem.textContent = displayCount + ' left';
    
    // Set appropriate status class and icon
    if (displayCount > 10) {
        statusSpan.className = 'badge bg-success';
        statusSpan.innerHTML = '<i class="fas fa-check-circle me-1"></i> In Stock';
    } else if (displayCount > 0) {
        statusSpan.className = 'badge bg-warning text-dark';
        statusSpan.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Low Stock';
    } else {
        statusSpan.className = 'badge bg-danger';
        statusSpan.innerHTML = '<i class="fas fa-times-circle me-1"></i> Out of Stock';
    }
}

});

// Setup quantity buttons with proper increment/decrement by 1
function setupQuantityButtons(modal) {
    const quantityInput = modal.querySelector('.quantity-input');
    const decrementBtn = modal.querySelector('.quantity-decrement');
    const incrementBtn = modal.querySelector('.quantity-increment');
    
    if (!quantityInput || !decrementBtn || !incrementBtn) return;
    
    // Disable default browser controls
    quantityInput.style.MozAppearance = 'textfield';
    quantityInput.style.appearance = 'textfield';
    
    // Remove existing event listeners (if any)
    const newDecrementBtn = decrementBtn.cloneNode(true);
    const newIncrementBtn = incrementBtn.cloneNode(true);
    decrementBtn.parentNode.replaceChild(newDecrementBtn, decrementBtn);
    incrementBtn.parentNode.replaceChild(newIncrementBtn, incrementBtn);
    
    // Set up new event listeners
    newDecrementBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let value = parseInt(quantityInput.value) || 1;
        const min = parseInt(quantityInput.min) || 1;
        if (value > min) {
            quantityInput.value = value - 1;
        }
        return false;
    });
    
    newIncrementBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let value = parseInt(quantityInput.value) || 1;
        const max = parseInt(quantityInput.max) || 999;
        if (value < max) {
            quantityInput.value = value + 1;
        }
        return false;
    });
    
    // Prevent default spinner behavior
    quantityInput.addEventListener('keydown', function(e) {
        if (e.which === 38 || e.which === 40) {
            e.preventDefault();
        }
    });
}

// Setup variant swapping functionality for a specific modal
function setupVariantSwapping(modal, productId) {
    // Main elements
    const mainImage = modal.querySelector('#mainDisplayImage' + productId);
    const selectButtons = modal.querySelectorAll('.select-item');
    const returnToMainButton = modal.querySelector('.return-to-main-product');
    const mainProductRow = modal.querySelector('.main-product-row');
    const variantRows = modal.querySelectorAll('.variant-row');
    const variantSpecificDetails = modal.querySelector('.variant-specific-details');
    const mainProductCTA = modal.querySelector('.add-to-cart-section');
    const currentVariantName = modal.querySelector('.current-variant-name');
    const variantsSectionTitle = modal.querySelector('.variants-section-title');
    const quantityInput = modal.querySelector('.quantity-input');
    const addToCartBtn = modal.querySelector('.add-to-cart-btn');
    const addToCartForm = modal.querySelector('form');
    const selectedVariantIdInput = modal.querySelector('.selected-variant-id');
    
    // Thumbnail container
    const thumbnailsContainer = modal.querySelector('.thumbnails-container');
    const productThumbs = modal.querySelectorAll('.product-thumb');
    
    if (!mainImage) return; // Skip if essential elements not found
    
    // Initialize with main product as default
    let currentlyViewingMain = true;
    let currentVariantId = null;
    
    // Store original thumbnails HTML for later use
    if (thumbnailsContainer) {
        thumbnailsContainer.dataset.originalHtml = thumbnailsContainer.innerHTML;
    }
    
    // Fix the blinking issue when modal opens by preloading content
    modal.addEventListener('show.bs.modal', function() {
        // Always reset to main product view when modal is going to be shown
        // This ensures that even if modal was closed while viewing a variant, 
        // it opens with the main product view
        switchToMainProduct();
    });
    
    // Setup quantity buttons after modal is fully shown
    modal.addEventListener('shown.bs.modal', function() {
        setupQuantityButtons(modal);
    });
    
    // Store original values
    const originalValues = {
        name: modal.querySelector('.main-display-name').textContent,
        description: modal.querySelector('.main-display-description').textContent,
        price: parseFloat(modal.querySelector('.current-price').textContent.replace('$', '')),
        imageUrl: mainImage ? mainImage.src : '',
        inventoryCount: parseInt(modal.querySelector('.inventory-count').textContent),
        maxQuantity: parseInt(quantityInput.getAttribute('max'))
    };
    
    // Setup event listeners for product thumbnails
    setupThumbnailClickHandlers(thumbnailsContainer, mainImage);
    
    // Setup event listeners for all variant select buttons
    selectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isMain = this.dataset.isMain === 'true';
            
            if (isMain) {
                // Switch back to main product
                switchToMainProduct();
            } else {
                // Switch to variant
                const variantId = this.dataset.variantId;
                const hasImages = this.dataset.hasImages === 'true';
                
                // Get variant data for display
                const variantData = {
                    name: this.dataset.name,
                    description: this.dataset.description,
                    price: parseFloat(this.dataset.price),
                    imageUrl: this.dataset.imageUrl,
                    inventoryCount: parseInt(this.dataset.inventory),
                    variantId: variantId,
                    hasImages: hasImages
                };
                
                switchToVariant(variantData);
            }
        });
    });
    
    // Setup return to main product button
    if (returnToMainButton) {
        returnToMainButton.addEventListener('click', function() {
            switchToMainProduct();
        });
    }
    
    // Function to switch to a variant
    function switchToVariant(variantData) {
        // Store current variant ID
        currentVariantId = variantData.variantId;
        
        // 1. Update main display area with smooth transition
        fadeElementContent(modal.querySelector('.main-display-content'), function() {
            modal.querySelector('.main-display-name').textContent = variantData.name;
            modal.querySelector('.main-display-description').textContent = variantData.description || originalValues.description;
            modal.querySelector('.current-price').textContent = '$' + variantData.price.toFixed(2);
            
            // Update inventory display
            updateInventoryDisplay(variantData.inventoryCount);
            
            // Update image if provided
            if (variantData.imageUrl && mainImage) {
                mainImage.src = variantData.imageUrl;
            }
            
            // Update current variant name
            if (currentVariantName) {
                currentVariantName.textContent = variantData.name;
            }
        });
        
        // 2. Show the main product in variants list, hide the variant
        if (mainProductRow) mainProductRow.style.display = 'table-row';
        
        // Hide the selected variant row, show others
        variantRows.forEach(row => {
            if (row.dataset.itemId === 'variant-' + variantData.variantId) {
                row.style.display = 'none';
            } else {
                row.style.display = 'table-row';
            }
        });
        
        // 3. Update UI state indicators
        if (variantSpecificDetails) variantSpecificDetails.style.display = 'block';
        if (variantsSectionTitle) variantsSectionTitle.textContent = 'Other Available Options';
        
        // 4. Update form for adding to cart
        if (quantityInput) {
            quantityInput.value = 1;
            quantityInput.max = variantData.inventoryCount;
        }
        if (selectedVariantIdInput) selectedVariantIdInput.value = variantData.variantId;
        
        // Toggle add to cart button based on inventory
        if (addToCartBtn) addToCartBtn.disabled = variantData.inventoryCount <= 0;
        
        // 5. Update tracking state
        currentlyViewingMain = false;
        
        // 6. Update thumbnails to show variant images
        if (thumbnailsContainer) {
            // Show loading indicator in thumbnails container
            thumbnailsContainer.innerHTML = '<div class="d-flex justify-content-center align-items-center w-100 p-3"><i class="fas fa-spinner fa-spin me-2"></i> Loading images...</div>';
            
            // Get all unique variant images - this prevents duplicates
            const variantImages = getVariantImages(productId, variantData.variantId);
            
            // After getting variant images, update the thumbnails
            setTimeout(() => {
                // Clear loading indicator
                thumbnailsContainer.innerHTML = '';
                
                if (variantImages.length > 0) {
                    // Create thumbnails for each image
                    variantImages.forEach((image, index) => {
                        const thumbHtml = `
                            <div class="thumbnail-wrapper variant-thumb ${index === 0 ? 'active-thumb' : ''}" 
                                 data-image-url="${image.url}">
                                <img src="${image.url}" 
                                     alt="${image.alt || variantData.name + ' thumbnail ' + (index + 1)}"
                                     class="variant-thumbnail">
                            </div>
                        `;
                        thumbnailsContainer.innerHTML += thumbHtml;
                    });
                } else if (variantData.imageUrl) {
                    // Fall back to just the main variant image
                    const thumbHtml = `
                        <div class="thumbnail-wrapper variant-thumb active-thumb" 
                             data-image-url="${variantData.imageUrl}">
                            <img src="${variantData.imageUrl}" 
                                 alt="${variantData.name} thumbnail"
                                 class="variant-thumbnail">
                        </div>
                    `;
                    thumbnailsContainer.innerHTML = thumbHtml;
                } else {
                    // No images available
                    thumbnailsContainer.innerHTML = '<div class="d-flex justify-content-center align-items-center w-100 p-3 text-muted"><i class="fas fa-image me-2"></i> No images available</div>';
                }
                
                // Setup click handlers for new thumbnails
                setupThumbnailClickHandlers(thumbnailsContainer, mainImage);
            }, 300); // Short delay to ensure smooth transition
        }
    }
    
    // Function to get all images for a variant
    function getVariantImages(productId, variantId) {
        const images = [];
        const processedUrls = new Set(); // Keep track of processed URLs to avoid duplicates
        
        // Find the variant row in the DOM
        const variantRow = document.querySelector(`.variant-row[data-item-id="variant-${variantId}"]`);
        if (!variantRow) return images;
        
        // Get the variant object from the DOM data
        const variantButton = variantRow.querySelector('.select-item');
        if (!variantButton) return images;
        
        // First, add the main variant image
        if (variantButton.dataset.imageUrl) {
            images.push({
                id: 0,
                url: variantButton.dataset.imageUrl,
                alt: variantButton.dataset.name || 'Variant image'
            });
            processedUrls.add(variantButton.dataset.imageUrl);
        }
        
        // Find all images for this variant
        try {
            // Get the actual variant from the product
            const variant = findVariantById(productId, variantId);
            
            if (variant && variant.images && variant.images.length > 0) {
                // Add all variant images, skipping duplicates
                variant.images.forEach((image, index) => {
                    // Format the image URL correctly
                    const imageUrl = image.image_url.startsWith('http') 
                        ? image.image_url 
                        : '/storage/' + image.image_url;
                    
                    // Skip if this URL is already in our collection
                    if (processedUrls.has(imageUrl)) {
                        return;
                    }
                    
                    // Add the image and mark URL as processed
                    images.push({
                        id: image.id || index + 1,
                        url: imageUrl,
                        alt: variant.name + ' image ' + (index + 1)
                    });
                    processedUrls.add(imageUrl);
                });
            }
        } catch (e) {
            console.error('Error getting variant images:', e);
        }
        
        return images;
    }
    
    // Helper function to find variant by id in the product data
    function findVariantById(productId, variantId) {
        // Get the product element
        const productModal = document.getElementById('productModal' + productId);
        if (!productModal) return null;
        
        // Find the variant row
        const variantRow = productModal.querySelector(`.variant-row[data-item-id="variant-${variantId}"]`);
        if (!variantRow) return null;
        
        // Create a variant object with extracted attributes
        const variant = {
            id: variantId,
            name: '',
            images: []
        };
        
        // Get variant name from the row
        const nameCell = variantRow.querySelector('td:nth-child(2)');
        if (nameCell) {
            variant.name = nameCell.textContent.trim();
        }
        
        // Get images directly from the DOM
        // First check if variant has its own image
        const mainImageCell = variantRow.querySelector('td:nth-child(1) img.variant-thumbnail');
        if (mainImageCell) {
            variant.images.push({
                id: 0,
                image_url: mainImageCell.getAttribute('src')
            });
        }
        
        // If we're using a data attribute with serialized images, parse it
        const selectButton = variantRow.querySelector('.select-item');
        if (selectButton && selectButton.dataset.variantImages) {
            try {
                const additionalImages = JSON.parse(selectButton.dataset.variantImages);
                if (Array.isArray(additionalImages)) {
                    additionalImages.forEach(img => {
                        variant.images.push(img);
                    });
                }
            } catch (e) {
                console.error('Error parsing variant images JSON:', e);
            }
        }
        
        return variant;
    }
    
    // Function to switch back to main product
    function switchToMainProduct() {
        // Reset current variant ID
        currentVariantId = null;
        
        // 1. Update main display area with smooth transition
        fadeElementContent(modal.querySelector('.main-display-content'), function() {
            modal.querySelector('.main-display-name').textContent = originalValues.name;
            modal.querySelector('.main-display-description').textContent = originalValues.description;
            modal.querySelector('.current-price').textContent = '$' + originalValues.price.toFixed(2);
            
            // Update inventory display
            updateInventoryDisplay(originalValues.maxQuantity);
            
            // Update image back to original
            if (mainImage && originalValues.imageUrl) {
                mainImage.src = originalValues.imageUrl;
            }
        });
        
        // 2. Hide the main product in variants list, show all variants
        if (mainProductRow) mainProductRow.style.display = 'none';
        variantRows.forEach(row => {
            row.style.display = 'table-row';
        });
        
        // 3. Update UI state indicators
        if (variantSpecificDetails) variantSpecificDetails.style.display = 'none';
        if (variantsSectionTitle) variantsSectionTitle.textContent = 'Choose a Variant';
        
        // 4. Update form for adding to cart
        if (quantityInput) {
            quantityInput.value = 1;
            quantityInput.max = originalValues.maxQuantity;
        }
        if (selectedVariantIdInput) selectedVariantIdInput.value = '';
        
        // Toggle add to cart button based on inventory
        if (addToCartBtn) addToCartBtn.disabled = originalValues.maxQuantity <= 0;
        
        // 5. Update tracking state
        currentlyViewingMain = true;
        
        // 6. Restore original thumbnails
        if (thumbnailsContainer && thumbnailsContainer.dataset.originalHtml) {
            thumbnailsContainer.innerHTML = thumbnailsContainer.dataset.originalHtml;
            setupThumbnailClickHandlers(thumbnailsContainer, mainImage);
            
            // Reset active thumbnail
            const allThumbs = thumbnailsContainer.querySelectorAll('.thumbnail-wrapper');
            allThumbs.forEach(thumb => thumb.classList.remove('active-thumb'));
            if (allThumbs.length > 0) {
                allThumbs[0].classList.add('active-thumb');
            }
        }
    }
    
    // Helper function to set up click handlers for thumbnails
    function setupThumbnailClickHandlers(container, targetImage) {
        if (!container || !targetImage) return;
        
        const thumbnails = container.querySelectorAll('.thumbnail-wrapper');
        thumbnails.forEach(thumb => {
            // Remove existing event listeners to prevent duplicates
            const newThumb = thumb.cloneNode(true);
            thumb.parentNode.replaceChild(newThumb, thumb);
            
            // Add new event listener
            newThumb.addEventListener('click', function(e) {
                e.preventDefault();
                const imageUrl = this.dataset.imageUrl;
                
                if (imageUrl && targetImage) {
                    targetImage.src = imageUrl;
                    
                    // Update active thumbnail - first remove active class from ALL current thumbnails
                    // This is the key fix - get a fresh collection of all thumbnails at click time
                    const allCurrentThumbnails = container.querySelectorAll('.thumbnail-wrapper');
                    allCurrentThumbnails.forEach(t => t.classList.remove('active-thumb'));
                    
                    // Then add active class to the clicked thumbnail
                    this.classList.add('active-thumb');
                }
            });
        });
    }
    
    // Helper function to update inventory display
    function updateInventoryDisplay(count) {
        const stockDisplay = modal.querySelector('.main-display-stock');
        const inventoryCountElem = modal.querySelector('.inventory-count');
        
        if (!stockDisplay || !inventoryCountElem) return;
        
        // Clear existing status classes and icons
        const statusSpan = stockDisplay.querySelector('span:first-child');
        if (!statusSpan) return;
        
        statusSpan.className = '';
        
        // Update inventory count
        inventoryCountElem.textContent = count + ' left';
        
        // Set appropriate status class and icon
        if (count > 10) {
            statusSpan.className = 'badge bg-success';
            statusSpan.innerHTML = '<i class="fas fa-check-circle me-1"></i> In Stock';
        } else if (count > 0) {
            statusSpan.className = 'badge bg-warning text-dark';
            statusSpan.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Low Stock';
        } else {
            statusSpan.className = 'badge bg-danger';
            statusSpan.innerHTML = '<i class="fas fa-times-circle me-1"></i> Out of Stock';
        }
    }
}

// Helper function to fade element content
function fadeElementContent(element, callback) {
    if (!element) return;
    
    // Add fade-out class
    element.classList.add('fade-content');
    
    // Wait for fade to complete
    setTimeout(() => {
        // Execute callback to update content
        if (callback && typeof callback === 'function') {
            callback();
        }
        
        // Remove fade-out class
        setTimeout(() => {
            element.classList.remove('fade-content');
        }, 50);
    }, 300);
}

// Helper function to format asset URLs
function asset(path) {
    // Check if path already starts with http or /
    if (path.startsWith('http') || path.startsWith('/')) {
        return path;
    }
    return '/storage/' + path;
}
</script>