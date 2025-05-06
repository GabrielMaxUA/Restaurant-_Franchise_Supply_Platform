@extends('layouts.franchisee')

@section('title', 'Order Details - Restaurant Supply Platform')

@section('page-title', 'Order Details')

@section('styles')
<style>
    .order-details-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
    }
    
    .order-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 15px 20px;
    }
    
    .order-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .order-number {
        font-size: 1.2rem;
        font-weight: 600;
    }
    
    .order-status {
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .status-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    
    .status-approved {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-rejected {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .status-packed {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    
    .status-shipped {
        background-color: #cce5ff;
        color: #004085;
    }
    
    .status-delivered {
        background-color: #d4edda;
        color: #155724;
    }
    
    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }
    
    .divider {
        height: 1px;
        background-color: #e9ecef;
        margin: 15px 0;
    }
    
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 15px;
    }
    
    .info-row {
        display: flex;
        margin-bottom: 10px;
    }
    
    .info-label {
        width: 150px;
        font-weight: 500;
        color: #6c757d;
    }
    
    .info-value {
        flex-grow: 1;
    }
    
    .order-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .order-item:last-child {
        border-bottom: none;
    }
    
    .item-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 8px;
        margin-right: 15px;
    }
    
    .item-details {
        flex-grow: 1;
    }
    
    .item-name {
        font-weight: 500;
    }
    
    .item-variant {
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .item-price {
        text-align: right;
    }
    
    .total-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
    }
    
    .total-label {
        font-weight: 500;
    }
    
    .grand-total {
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .action-btn {
        transition: all 0.3s ease;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 7px;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    
    .timeline-dot {
        position: absolute;
        left: -30px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background-color: #28a745;
    }
    
    .timeline-date {
        font-size: 0.9rem;
        color: #6c757d;
        margin-bottom: 5px;
    }
    
    .timeline-content {
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Details Card -->
            <div class="card order-details-card">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-number">
                            Order #{{ $order->id }}
                        </div>
                        <div class="order-status status-{{ $order->status }}">
                            {{ ucfirst($order->status) }}
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Order Date and Total -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Order Date</div>
                                <div class="info-value">{{ $order->created_at->format('F j, Y, g:i a') }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Total Amount</div>
                                <div class="info-value">${{ number_format($order->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="section-title">Shipping Information</div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Address</div>
                                <div class="info-value">{{ $order->shipping_address }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">City</div>
                                <div class="info-value">{{ $order->shipping_city }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">State</div>
                                <div class="info-value">{{ $order->shipping_state }}</div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">ZIP Code</div>
                                <div class="info-value">{{ $order->shipping_zip }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">Delivery Date</div>
                                <div class="info-value">
                                    @if($order->delivery_date)
                                        {{ \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') }}
                                    @else
                                        Not specified
                                    @endif
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Delivery Time</div>
                                <div class="info-value">
                                    @if($order->delivery_time == 'morning')
                                        Morning (8:00 AM - 12:00 PM)
                                    @elseif($order->delivery_time == 'afternoon')
                                        Afternoon (12:00 PM - 4:00 PM)
                                    @elseif($order->delivery_time == 'evening')
                                        Evening (4:00 PM - 8:00 PM)
                                    @else
                                        {{ $order->delivery_time ?? 'Not specified' }}
                                    @endif
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Delivery Method</div>
                                <div class="info-value">
                                    @if($order->delivery_preference == 'standard')
                                        Standard Delivery (3-5 business days)
                                    @elseif($order->delivery_preference == 'express')
                                        Express Delivery (1-2 business days)
                                    @elseif($order->delivery_preference == 'scheduled')
                                        Scheduled Delivery
                                    @else
                                        {{ $order->delivery_preference ?? 'Standard' }}
                                    @endif
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Contact</div>
                                <div class="info-value">{{ $order->contact_phone ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    @if($order->notes)
                    <div class="section-title">Order Notes</div>
                    <div class="row mb-4">
                        <div class="col-12">
                            <p>{{ $order->notes }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="divider"></div>
                    
                    <!-- Order Items -->
                    <div class="section-title">Order Items</div>
                    
                    @foreach($order->items as $item)
                        <div class="order-item">
                            <div class="item-image-container">
                                @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image_url) }}" alt="{{ $item->product->name }}" class="item-image">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center item-image">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="item-details">
                                <div class="item-name">{{ $item->product->name ?? 'Product Not Available' }}</div>
                                @if($item->variant)
                                    <div class="item-variant">{{ $item->variant->name }}</div>
                                @endif
                                <div class="text-muted">Qty: {{ $item->quantity }}</div>
                            </div>
                            <div class="item-price">
                                <div>${{ number_format($item->price, 2) }}</div>
                                <div class="text-success">${{ number_format($item->price * $item->quantity, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="divider"></div>
                    
                    <!-- Order Totals -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="total-row">
                                <div class="total-label">Subtotal</div>
                                <div>${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</div>
                            </div>
                            <div class="total-row">
                                <div class="total-label">Shipping</div>
                                <div>${{ number_format($order->shipping_cost ?? 0, 2) }}</div>
                            </div>
                            <div class="divider"></div>
                            <div class="total-row grand-total">
                                <div>Total</div>
                                <div>${{ number_format($order->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Actions -->
            @if($order->status == 'pending')
            <div class="card order-details-card">
                <div class="card-body">
                    <div class="section-title">Order Actions</div>
                    <div class="d-flex">
                        <form action="{{ route('franchisee.orders.cancel', $order->id) }}" method="POST" class="me-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger action-btn" onclick="return confirm('Are you sure you want to cancel this order?');">
                                <i class="fas fa-times-circle me-2"></i> Cancel Order
                            </button>
                        </form>
                        
                        <a href="{{ route('franchisee.orders.modify', $order->id) }}" class="btn btn-outline-primary action-btn me-2">
                            <i class="fas fa-edit me-2"></i> Modify Order
                        </a>
                        
                        <a href="{{ route('franchisee.orders.invoice', $order->id) }}" class="btn btn-outline-secondary action-btn" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download Invoice
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-lg-4">
            <!-- Order Status Card -->
            <div class="card order-details-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-date">{{ $order->created_at->format('F j, Y, g:i a') }}</div>
                            <div class="timeline-content">Order Placed</div>
                        </div>
                        
                        @if($order->status != 'pending' && $order->status != 'rejected' && $order->status != 'cancelled')
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">{{ $order->updated_at->format('F j, Y, g:i a') }}</div>
                                <div class="timeline-content">Order Approved</div>
                            </div>
                        @endif
                        
                        @if($order->status == 'packed' || $order->status == 'shipped' || $order->status == 'delivered')
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">{{ $order->updated_at->format('F j, Y, g:i a') }}</div>
                                <div class="timeline-content">Order Packed</div>
                            </div>
                        @endif
                        
                        @if($order->status == 'shipped' || $order->status == 'delivered')
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">{{ $order->updated_at->format('F j, Y, g:i a') }}</div>
                                <div class="timeline-content">Order Shipped</div>
                            </div>
                        @endif
                        
                        @if($order->status == 'delivered')
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">{{ $order->updated_at->format('F j, Y, g:i a') }}</div>
                                <div class="timeline-content">Order Delivered</div>
                            </div>
                        @endif
                        
                        @if($order->status == 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">{{ $order->updated_at->format('F j, Y, g:i a') }}</div>
                                <div class="timeline-content">Order Rejected</div>
                            </div>
                        @endif
                        
                        @if($order->status == 'cancelled')
                            <div class="timeline-item">
                                <div class="timeline-dot"></div>
                                <div class="timeline-date">{{ $order->updated_at->format('F j, Y, g:i a') }}</div>
                                <div class="timeline-content">Order Cancelled</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Delivery Information -->
            <div class="card order-details-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-1"><i class="fas fa-truck text-primary me-2"></i> Estimated delivery: 
                        @if($order->delivery_date)
                            {{ \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') }}
                        @else
                            3-5 business days
                        @endif
                    </p>
                    
                    <p class="mb-1"><i class="fas fa-map-marker-alt text-primary me-2"></i> Shipping to: 
                        {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                    </p>
                    
                    <p class="mb-0"><i class="fas fa-phone text-primary me-2"></i> Contact: {{ $order->contact_phone ?? 'Not provided' }}</p>
                </div>
            </div>
            
            <!-- Need Help? -->
            <div class="card order-details-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-headset me-2"></i> For any questions about your order, please contact our customer support:</p>
                    <p><strong>Email:</strong> support@restaurantsupply.com</p>
                    <p><strong>Phone:</strong> (555) 123-4567</p>
                    <p><strong>Hours:</strong> Monday - Friday, 9:00 AM - 5:00 PM EST</p>
                    
                    <hr>
                    
                    <a href="{{ route('franchisee.orders.pending') }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-list me-2"></i> View All Orders
                    </a>
                    
                    <a href="{{ route('franchisee.catalog') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection