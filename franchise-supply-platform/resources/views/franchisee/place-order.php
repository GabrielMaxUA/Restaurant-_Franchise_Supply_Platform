@extends('layouts.franchisee')

@section('title', 'Place Order - Restaurant Supply Platform')

@section('page-title', 'Place Order')

@section('styles')
<style>
    .order-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        padding: 20px;
    }
    
    .order-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }
    
    .order-step {
        background-color: #f8f9fa;
        border-radius: 5px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .step-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: #28a745;
    }
    
    .required-field::after {
        content: "*";
        color: #dc3545;
        margin-left: 4px;
    }
    
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
    }
    
    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .place-order-btn {
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
        margin-top: 20px;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        color: #007bff;
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 20px;
    }
    
    .back-link i {
        margin-right: 5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Back to Cart Link -->
    <a href="{{ route('franchisee.cart') }}" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Cart
    </a>
    
    <div class="row">
        <!-- Order Form -->
        <div class="col-lg-8 mb-4">
            <div class="order-container">
                <h2 class="order-title">Complete Your Order</h2>
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <form id="order-form" action="{{ route('franchisee.cart.place.order') }}" method="POST">
                    @csrf
                    
                    <!-- Shipping Information -->
                    <div class="order-step">
                        <div class="step-title">Shipping Information</div>
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label required-field">Street Address</label>
                            <input type="text" class="form-control" id="shipping_address" name="shipping_address" 
                                required value="{{ old('shipping_address') }}">
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="shipping_city" class="form-label required-field">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city" 
                                    required value="{{ old('shipping_city') }}">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="shipping_state" class="form-label required-field">State</label>
                                <input type="text" class="form-control" id="shipping_state" name="shipping_state" 
                                    required value="{{ old('shipping_state') }}">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="shipping_zip" class="form-label required-field">ZIP Code</label>
                                <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" 
                                    required value="{{ old('shipping_zip') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Options -->
                    <div class="order-step">
                        <div class="step-title">Delivery Options</div>
                        
                        <div class="mb-3">
                            <label for="delivery_date" class="form-label required-field">Delivery Date</label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date" 
                                required value="{{ old('delivery_date', date('Y-m-d', strtotime('+3 day'))) }}">
                        </div>
                        
                        <div class="mb-3">
                            <label for="delivery_time" class="form-label">Preferred Time</label>
                            <select class="form-select" id="delivery_time" name="delivery_time">
                                <option value="morning" selected>Morning (8:00 AM - 12:00 PM)</option>
                                <option value="afternoon">Afternoon (12:00 PM - 4:00 PM)</option>
                                <option value="evening">Evening (4:00 PM - 8:00 PM)</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Delivery Preference</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_preference" 
                                    id="standard_delivery" value="standard" checked>
                                <label class="form-check-label" for="standard_delivery">
                                    Standard Delivery (Free)
                                </label>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="delivery_preference" 
                                    id="express_delivery" value="express">
                                <label class="form-check-label" for="express_delivery">
                                    Express Delivery (+$15.00)
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Special Instructions</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    
                    <!-- Hidden default fields -->
                    <input type="hidden" name="manager_name" value="Default Manager">
                    <input type="hidden" name="contact_phone" value="1234567890">
                    
                    <button type="submit" class="place-order-btn">
                        <i class="fas fa-check-circle me-2"></i> Place Order
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="order-summary">
                <h3 class="order-summary-title">Order Summary</h3>
                
                <div class="order-items">
                    @foreach($cartItems ?? [] as $item)
                        <div class="order-item">
                            <div>
                                <div>{{ $item['product']->name }}</div>
                                @if($item['variant'])
                                    <small>{{ $item['variant']->name }}</small>
                                @endif
                                <small>Qty: {{ $item['quantity'] }}</small>
                            </div>
                            <div>${{ number_format($item['subtotal'], 2) }}</div>
                        </div>
                    @endforeach
                </div>
                
                <div class="d-flex justify-content-between mt-3">
                    <span>Subtotal:</span>
                    <span>${{ number_format($total ?? 0, 2) }}</span>
                </div>
                
                <div class="d-flex justify-content-between">
                    <span>Shipping:</span>
                    <span id="shipping-cost">$0.00</span>
                </div>
                
                <div class="d-flex justify-content-between mt-3 fw-bold">
                    <span>Total:</span>
                    <span id="total-cost">${{ number_format($total ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const standardDelivery = document.getElementById('standard_delivery');
        const expressDelivery = document.getElementById('express_delivery');
        const shippingCostElement = document.getElementById('shipping-cost');
        const totalCostElement = document.getElementById('total-cost');
        
        function updateTotals() {
            const subtotal = parseFloat('{{ $total ?? 0 }}');
            let shippingCost = 0;
            
            if (expressDelivery.checked) {
                shippingCost = 15.00;
            }
            
            const total = subtotal + shippingCost;
            
            shippingCostElement.textContent = '$' + shippingCost.toFixed(2);
            totalCostElement.textContent = '$' + total.toFixed(2);
        }
        
        // Add change event listeners
        standardDelivery.addEventListener('change', updateTotals);
        expressDelivery.addEventListener('change', updateTotals);
        
        // Initialize totals on page load
        updateTotals();
    });
</script>
@endsection