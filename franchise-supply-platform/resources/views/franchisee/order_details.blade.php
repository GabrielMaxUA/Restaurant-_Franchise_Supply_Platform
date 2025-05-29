@extends('layouts.franchisee')

@section('title', 'Order Details - Restaurant Supply Platform')
@section('page-title', 'Order Details')

@section('styles')
    <link href="{{ asset('css/status-styles.css') }}" rel="stylesheet">
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

    .status-pending { background-color: #fff3cd; color: #856404; }
    .status-approved { background-color: #d4edda; color: #155724; }
    .status-packed { background-color: #d1ecf1; color: #0c5460; }
    .status-shipped { background-color: #cce5ff; color: #004085; }
    .status-delivered { background-color: #d4edda; color: #155724; }
    .status-rejected, .status-cancelled { background-color: #f8d7da; color: #721c24; }

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

    /* Order progress tracker styling - optimized for narrower container */
    .order-tracker {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin: 30px 0;
        padding: 0 15px; /* Reduced padding for narrower container */
        min-height: 70px; /* Reduced for smaller circles */
    }

    .order-tracker:before {
        content: '';
        position: absolute;
        background: #e5e5e5;
        height: 3px; /* Slightly thinner line */
        width: calc(100% - 30px); /* Adjusted for reduced padding */
        top: 15px; /* Adjusted for smaller circles (30px/2 = 15px) */
        left: 15px; /* Match reduced padding */
        z-index: 1;
        border-radius: 2px;
    }
    
    .tracker-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 3;
        flex: 1;
        max-width: 20%;
    }
    
    .step-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 30px; /* Reduced from 50px */
        height: 30px; /* Reduced from 50px */
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e5e5e5; /* Reduced border width */
        margin-bottom: 8px; /* Reduced margin */
        transition: all 0.3s ease;
        color: #888;
        font-size: 12px; /* Reduced font size */
        position: relative;
        z-index: 3;
    }
    
    .step-label {
        font-size: 13px;
        font-weight: 500;
        color: #888;
        text-align: center;
    }
    
    .tracker-step.active .step-icon {
        background: #4CAF50;
        border-color: #4CAF50;
        color: white;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
    }
    
    .tracker-step.active .step-label {
        color: #4CAF50;
        font-weight: 600;
    }
    
    .tracker-step.completed .step-icon {
        background: #4CAF50;
        border-color: #4CAF50;
        color: white;
    }
    
    .tracker-step.completed .step-label {
        color: #4CAF50;
    }
    
    /* Progress line styling */
    .progress-line {
        position: absolute;
        top: 15px; /* Adjusted for smaller circles */
        height: 3px; /* Slightly thinner line */
        background: #4CAF50;
        z-index: 2;
        transition: width 0.5s ease, background-color 0.5s ease;
        left: 15px; /* Match reduced padding */
        border-radius: 2px;
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.1); /* Reduced shadow */
    }
    
    /* Rejected order styling */
    .order-tracker.rejected:before {
        background: #e5e5e5;
    }
    
    .progress-line.rejected {
        background: #dc3545;
    }
    
    .tracker-step.rejected .step-icon {
        background: #dc3545;
        border-color: #dc3545;
        color: white;
        box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
    }
    
    .tracker-step.rejected .step-label {
        color: #dc3545;
        font-weight: 600;
    }
    
    .step-date {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 2px;
        min-height: 32px; /* Reserve space for date and time */
        text-align: center;
        line-height: 1.2;
    }
    
    .step-date-day {
        display: block;
        font-weight: 500;
    }
    
    .step-date-time {
        display: block;
        font-size: 0.7rem;
        opacity: 0.8;
    }

    /* Rejected order action buttons container */
    .rejected-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .rejected-actions .btn {
    width: 50%;
    display: flex;
    align-items: center;
    font-weight: 500;
  }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <!-- @if(session('success'))
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
    @endif -->

    <div class="row">
        <div class="col-lg-8">
            <!-- Order Details Card -->
            <div class="card order-details-card">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-number">Order #{{ $order->id }}</div>
                        @if($order->status == 'pending')
                            <span class="status-badge status-pending">Pending</span>
                        @elseif($order->status == 'processing' || $order->status == 'approved')
                            <span class="status-badge status-processing">Processing</span>
                        @elseif($order->status == 'packed')
                            <span class="status-badge status-packed">Packed</span>
                        @elseif($order->status == 'shipped')
                            <span class="status-badge status-shipped">Shipped</span>
                        @elseif($order->status == 'delivered')
                            <span class="status-badge status-delivered">Delivered</span>
                        @elseif($order->status == 'rejected')
                            <span class="status-badge status-rejected">Rejected</span>
                        @else
                            <span class="status-badge status-secondary">{{ ucfirst($order->status) }}</span>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <!-- Order Date and Totals -->
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

                    <!-- Shipping Info -->
                    <div class="section-title">Shipping Information</div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-row"><div class="info-label">Address</div><div class="info-value">{{ $order->shipping_address }}</div></div>
                            <div class="info-row"><div class="info-label">City</div><div class="info-value">{{ $order->shipping_city }}</div></div>
                            <div class="info-row"><div class="info-label">State</div><div class="info-value">{{ $order->shipping_state }}</div></div>
                            <div class="info-row"><div class="info-label">ZIP</div><div class="info-value">{{ $order->shipping_zip }}</div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <div class="info-label">{{ $order->status == 'delivered' ? 'Delivered On' : 'Delivery Date' }}</div>
                                <div class="info-value">
                                    @if($order->status == 'delivered' && $order->delivered_at)
                                        {{ $order->delivered_at->format('F j, Y') }}
                                    @else
                                        {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : 'Not specified' }}
                                    @endif
                                </div>
                            </div>
                            <div class="info-row"><div class="info-label">Delivery Time</div><div class="info-value">{{ $order->delivery_time ?? 'Not specified' }}</div></div>
                            <div class="info-row"><div class="info-label">Delivery Method</div><div class="info-value">{{ $order->delivery_preference ?? 'Standard' }}</div></div>
                            <div class="info-row"><div class="info-label">Contact</div><div class="info-value">{{ $order->contact_phone ?? 'Not provided' }}</div></div>
                        </div>
                    </div>

                    @if($order->notes)
                        <div class="section-title">Order Notes</div>
                        <p>{{ $order->notes }}</p>
                    @endif

                    <div class="divider"></div>

                    <!-- Order Items -->
                    <div class="section-title">Order Items</div>
                    @foreach($order->items as $item)
                        <div class="order-item">
                            @if($item->product && $item->product->images->first())
                                <img src="{{ asset('storage/' . $item->product->images->first()->image_url) }}" class="item-image" alt="">
                            @else
                                <div class="item-image bg-light d-flex justify-content-center align-items-center"><i class="fas fa-image text-muted"></i></div>
                            @endif
                            <div class="item-details">
                                <div class="item-name">{{ $item->product->name ?? 'Product Not Available' }}</div>
                                @if($item->variant)<div class="item-variant">{{ $item->variant->name }}</div>@endif
                                <div class="text-muted">Qty: {{ $item->quantity }}</div>
                            </div>
                            <div class="item-price">
                                <div>${{ number_format($item->price, 2) }}</div>
                                <div class="text-success">${{ number_format($item->price * $item->quantity, 2) }}</div>
                            </div>
                        </div>
                    @endforeach

                    <div class="divider"></div>

                    <!-- Totals -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="total-row"><div class="total-label">Subtotal</div><div>${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</div></div>
                            <div class="total-row"><div class="total-label">Shipping</div><div>${{ number_format($order->shipping_cost ?? 0, 2) }}</div></div>
                            <div class="divider"></div>
                            <div class="total-row grand-total"><div>Total</div><div>${{ number_format($order->total_amount, 2) }}</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card order-details-card">
                <div class="card-body">
                    <div class="section-title">Order Actions</div>
                    <div class="action-buttons">
                        @if(!in_array($order->status, ['pending', 'rejected', 'cancelled']))
                            <a href="{{ route('franchisee.orders.invoice', ['id' => $order->id]) }}?print=true" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-invoice me-2"></i> View & Print Invoice
                            </a>
                        @else
                            <button class="btn btn-outline-secondary" disabled>
                                <i class="fas fa-file-invoice me-2"></i> View & Print Invoice
                            </button>
                        @endif

                        @if($order->status === 'pending')
                            <small class="text-muted ms-2 d-flex align-items-center">
                                <i class="fas fa-info-circle me-1"></i> Invoice will be available once approved
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="col-lg-4">
            <div class="card order-details-card">
                <div class="card-header"><h5 class="card-title mb-0">Order Status</h5></div>
                <div class="card-body">
                    <!-- Order Progress Tracker -->
                    <div class="position-relative">
                        <div class="order-tracker {{ in_array($order->status, ['rejected', 'cancelled']) ? 'rejected' : '' }}">
                            <!-- Progress line that fills based on order status -->
                            @php
                                // Calculate progress width based on order status
                                $progressWidth = 0;
                                $progressColor = '#4CAF50'; // Default green
                                $numSteps = 5; // Total number of steps in the progress bar
                                
                                // Calculate the width to reach each circle center for narrower container
                                // The full width minus reduced padding is divided into 4 segments (between 5 circles)
                                $containerWidth = 'calc(100% - 30px)';
                                
                                if($order->status == 'pending') {
                                    $progressWidth = '0px'; // Stay at first circle
                                }
                                elseif($order->status == 'processing' || $order->status == 'approved') {
                                    $progressWidth = 'calc((100% - 30px) * 0.25)'; // 25% of container width
                                }
                                elseif($order->status == 'packed') {
                                    $progressWidth = 'calc((100% - 30px) * 0.5)'; // 50% of container width
                                }
                                elseif($order->status == 'shipped') {
                                    $progressWidth = 'calc((100% - 30px) * 0.75)'; // 75% of container width
                                }
                                elseif($order->status == 'delivered') {
                                    $progressWidth = 'calc(100% - 30px)'; // Full container width
                                }
                                elseif($order->status == 'rejected' || $order->status == 'cancelled') {
                                    $progressWidth = 'calc((100% - 30px) * 0.25)'; // Stop at second circle
                                    $progressColor = '#dc3545'; // Red for rejected/cancelled
                                }
                            @endphp
                            <div class="progress-line" style="width: {{ $progressWidth }}; background-color: {{ $progressColor }};"></div>
                            
                            <!-- Step 1: Pending -->
                            <div class="tracker-step {{ $order->status == 'pending' ? 'active' : ($order->status == 'rejected' || $order->status == 'cancelled' ? '' : 'completed') }}">
                                <div class="step-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="step-label">Pending</div>
                                <div class="step-date">
                                    <span class="step-date-day">{{ $order->created_at->format('M j, Y') }}</span>
                                    <span class="step-date-time">{{ $order->created_at->format('g:i A') }}</span>
                                </div>
                            </div>

                            <!-- Step 2: Processing/Approved -->
                            <div class="tracker-step {{ $order->status == 'processing' || $order->status == 'approved' ? 'active' :
                                        ($order->status == 'rejected' || $order->status == 'cancelled' ? 'rejected' :
                                        (in_array($order->status, ['packed', 'shipped', 'delivered']) ? 'completed' : '')) }}">
                                <div class="step-icon">
                                    @if($order->status == 'rejected' || $order->status == 'cancelled')
                                        <i class="fas fa-times"></i>
                                    @else
                                        <i class="fas fa-cogs"></i>
                                    @endif
                                </div>
                                <div class="step-label">
                                    @if($order->status == 'rejected')
                                        Rejected
                                    @elseif($order->status == 'cancelled')
                                        Cancelled
                                    @else
                                        Approved
                                    @endif
                                </div>
                                <div class="step-date">
                                    @if(in_array($order->status, ['rejected', 'cancelled', 'processing', 'approved', 'packed', 'shipped', 'delivered']))
                                        <span class="step-date-day">{{ $order->updated_at->format('M j, Y') }}</span>
                                        <span class="step-date-time">{{ $order->updated_at->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 3: Packed -->
                            <div class="tracker-step {{ $order->status == 'packed' ? 'active' :
                                        (in_array($order->status, ['shipped', 'delivered']) ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="step-label">Packed</div>
                                <div class="step-date">
                                    @if(in_array($order->status, ['packed', 'shipped', 'delivered']))
                                        <span class="step-date-day">{{ $order->updated_at->format('M j, Y') }}</span>
                                        <span class="step-date-time">{{ $order->updated_at->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 4: Shipped -->
                            <div class="tracker-step {{ $order->status == 'shipped' ? 'active' :
                                        ($order->status == 'delivered' ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="step-label">Shipped</div>
                                <div class="step-date">
                                    @if(in_array($order->status, ['shipped', 'delivered']))
                                        <span class="step-date-day">{{ $order->updated_at->format('M j, Y') }}</span>
                                        <span class="step-date-time">{{ $order->updated_at->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Step 5: Delivered -->
                            <div class="tracker-step {{ $order->status == 'delivered' ? 'active' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="step-label">Delivered</div>
                                <div class="step-date">
                                    @if($order->status == 'delivered')
                                        <span class="step-date-day">{{ $order->updated_at->format('M j, Y') }}</span>
                                        <span class="step-date-time">{{ $order->updated_at->format('g:i A') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delivery Info + Help -->
            @if($order->status == 'rejected')
            <!-- Action Buttons for Rejected Orders -->
            <div class="card order-details-card">
                <div class="card-body">
                    <div class="rejected-actions">
                        <a href="{{ route('franchisee.orders.pending') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i> View All Orders
                        </a>
                        <a href="{{ route('franchisee.catalog') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart me-2"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="card order-details-card">
                <div class="card-header"><h5 class="card-title mb-0">Delivery Information</h5></div>
                <div class="card-body">
                    <p><i class="fas fa-truck text-primary me-2"></i> 
                        @if($order->status == 'delivered' && $order->delivered_at)
                            Delivered on: {{ $order->delivered_at->format('F j, Y \a\t g:i A') }}
                        @else
                            Estimated delivery: {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : '3-5 business days' }}
                        @endif
                    </p>
                    <p><i class="fas fa-map-marker-alt text-primary me-2"></i>
                        Shipping to: {{ $order->shipping_address }}, {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                    </p>
                    <p><i class="fas fa-phone text-primary me-2"></i>
                        Contact: {{ $order->contact_phone ?? 'Not provided' }}
                    </p>

                    <hr>
                    <a href="{{ route('franchisee.orders.pending') }}" class="btn btn-outline-secondary w-100 mb-2">
                        <i class="fas fa-list me-2"></i> View All Orders
                    </a>
                    <a href="{{ route('franchisee.catalog') }}" class="btn btn-outline-primary w-100">
                        <i class="fas fa-shopping-bag me-2"></i> Continue Shopping
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection