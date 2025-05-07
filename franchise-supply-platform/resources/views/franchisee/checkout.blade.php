@extends('layouts.franchisee')

@section('title', 'Checkout - Restaurant Supply Platform')

@section('page-title', 'Checkout')

@section('styles')
<style>
    .checkout-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .order-summary {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
    }
    
    .cart-item {
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 0;
    }
    
    .cart-item:last-child {
        border-bottom: none;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
    }
    
    .input-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        border-color: #28a745;
    }
    
    .btn-checkout {
        background: linear-gradient(45deg, #28a745, #20c997);
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-checkout:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
        </div>
    @endif
    
    <div class="row">
        <!-- Checkout Form -->
        <div class="col-lg-8 mb-4">
            <div class="card checkout-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('franchisee.cart.place-order') }}" method="POST" id="checkout-form">
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="use_franchise_address" name="use_franchise_address">
                                    <label class="form-check-label" for="use_franchise_address">
                                        Use my franchise address for shipping
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="shipping_address" class="input-label">Shipping Address</label>
                                <input type="text" class="form-control @error('shipping_address') is-invalid @enderror" 
                                       id="shipping_address" name="shipping_address" 
                                       value="{{ old('shipping_address', '') }}" required>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="shipping_city" class="input-label">City</label>
                                <input type="text" class="form-control @error('shipping_city') is-invalid @enderror" 
                                       id="shipping_city" name="shipping_city" 
                                       value="{{ old('shipping_city', '') }}" required>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="shipping_state" class="input-label">State</label>
                                <input type="text" class="form-control @error('shipping_state') is-invalid @enderror" 
                                       id="shipping_state" name="shipping_state" 
                                       value="{{ old('shipping_state', '') }}" required>
                                @error('shipping_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="shipping_zip" class="input-label">ZIP Code</label>
                                <input type="text" class="form-control @error('shipping_zip') is-invalid @enderror" 
                                       id="shipping_zip" name="shipping_zip" 
                                       value="{{ old('shipping_zip', '') }}" required>
                                @error('shipping_zip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="notes" class="input-label">Order Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="delivery_preference" class="input-label">Delivery Preference</label>
                                <select class="form-select @error('delivery_preference') is-invalid @enderror" 
                                        id="delivery_preference" name="delivery_preference">
                                    <option value="standard">Standard Delivery (3-5 business days)</option>
                                    <option value="express">Express Delivery (1-2 business days) +$15.00</option>
                                    <option value="scheduled">Scheduled Delivery (Choose a specific date)</option>
                                </select>
                                @error('delivery_preference')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row mb-3 delivery-date-container d-none">
                            <div class="col-md-6">
                                <label for="delivery_date" class="input-label">Preferred Delivery Date</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date"
                                       min="{{ date('Y-m-d', strtotime('+3 days')) }}">
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="{{ route('franchisee.cart') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i> Back to Cart
                                </a>
                            </div>
                            <div class="col-md-6 text-end">
                                <button type="submit" class="btn btn-success btn-lg btn-checkout">
                                    <i class="fas fa-check-circle me-2"></i> Place Order
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Order Summary section remains unchanged -->
        <div class="col-lg-4">
            <div class="card checkout-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="order-summary">
                        <h6 class="mb-3">Items ({{ count($cartItems) }})</h6>
                        
                        @foreach($cartItems as $item)
                            <div class="cart-item">
                                <div class="d-flex">
                                    <div class="flex-shrink-0 me-3">
                                        @if($item['product']->images && $item['product']->images->count() > 0)
                                            <img src="{{ asset('storage/' . $item['product']->images->first()->image_url) }}" 
                                                alt="{{ $item['product']->name }}" class="item-image">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center item-image">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0">{{ $item['product']->name }}</h6>
                                        @if($item['variant'])
                                            <small class="text-muted">{{ $item['variant']->name }}</small>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="text-muted">Qty: {{ $item['quantity'] }}</span>
                                            <span class="text-success">${{ number_format($item['subtotal'], 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span id="shipping-cost">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 tax-row">
                                <span>Tax (8%):</span>
                                <span>${{ number_format($total * 0.08, 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-0">
                                <strong>Total:</strong>
                                <strong id="order-total">${{ number_format($total + ($total * 0.08), 2) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Information card remains unchanged -->
            <div class="card checkout-card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><i class="fas fa-info-circle text-primary me-2"></i> Orders are typically delivered within 3-5 business days.</p>
                    <p class="mb-1"><i class="fas fa-truck text-primary me-2"></i> Free standard shipping on all orders.</p>
                    <p class="mb-0"><i class="fas fa-phone text-primary me-2"></i> For delivery questions, contact our logistics team at support@restaurantsupply.com</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Checkout page loaded');
        
        // Get form elements
        const useAddressCheckbox = document.getElementById('use_franchise_address');
        const shippingAddressInput = document.getElementById('shipping_address');
        const shippingCityInput = document.getElementById('shipping_city');
        const shippingStateInput = document.getElementById('shipping_state');
        const shippingZipInput = document.getElementById('shipping_zip');
        
        // Function to toggle address fields based on checkbox
        function toggleAddressFields() {
            if (useAddressCheckbox.checked) {
                console.log('Attempting to fetch franchisee address');
                
                // Use AJAX to get the franchisee address
                fetch('{{ route("franchisee.get-address") }}')
                    .then(response => response.json())
                    .then(data => {
                        console.log('Received address data:', data);
                        
                        if (data.success) {
                            // Fill the form fields with the fetched data
                            shippingAddressInput.value = data.address || '';
                            shippingCityInput.value = data.city || '';
                            shippingStateInput.value = data.state || '';
                            shippingZipInput.value = data.postal_code || '';
                            
                            // Make fields read-only
                            shippingAddressInput.readOnly = true;
                            shippingCityInput.readOnly = true;
                            shippingStateInput.readOnly = true;
                            shippingZipInput.readOnly = true;
                        } else {
                            console.error('Error fetching address:', data.message);
                            // Show an error message to the user
                            alert('Could not retrieve your franchise address. Please enter it manually.');
                            useAddressCheckbox.checked = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Show an error message to the user
                        alert('Could not retrieve your franchise address. Please enter it manually.');
                        useAddressCheckbox.checked = false;
                    });
            } else {
                // Clear the fields and make them editable
                shippingAddressInput.readOnly = false;
                shippingCityInput.readOnly = false;
                shippingStateInput.readOnly = false;
                shippingZipInput.readOnly = false;
                
                // Clear the fields
                shippingAddressInput.value = '';
                shippingCityInput.value = '';
                shippingStateInput.value = '';
                shippingZipInput.value = '';
            }
        }
        
        // Add event listener to checkbox
        useAddressCheckbox.addEventListener('change', toggleAddressFields);
        
        // Show/hide delivery date picker based on delivery preference
        const deliveryPreference = document.getElementById('delivery_preference');
        const deliveryDateContainer = document.querySelector('.delivery-date-container');
        
        deliveryPreference.addEventListener('change', function() {
            if (this.value === 'scheduled') {
                deliveryDateContainer.classList.remove('d-none');
            } else {
                deliveryDateContainer.classList.add('d-none');
            }
            
            // Update shipping cost based on delivery preference
            const shippingCostEl = document.getElementById('shipping-cost');
            const orderTotalEl = document.getElementById('order-total');
            const subtotal = {{ $total }};
            const tax = subtotal * 0.08;
            let shippingCost = 0;
            
            if (this.value === 'express') {
                shippingCost = 15.00;
            }
            
            shippingCostEl.textContent = '$' + shippingCost.toFixed(2);
            orderTotalEl.textContent = '$' + (subtotal + tax + shippingCost).toFixed(2);
        });
    });
</script>
@endsection