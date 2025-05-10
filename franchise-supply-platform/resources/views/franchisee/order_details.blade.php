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

    .progress-track {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
        margin: 30px 0 10px;
    }

    .progress-line {
        position: absolute;
        top: 6px;
        left: 0;
        height: 4px;
        width: 100%;
        background-color: #dee2e6;
        z-index: 1;
        border-radius: 2px;
    }

    .progress-line-filled {
        background-color: #28a745;
        height: 4px;
        border-radius: 2px;
        z-index: 2;
        position: absolute;
        top: 6px;
        left: 0;
        transition: width 0.4s ease-in-out;
    }

    .progress-line-filled.rejected {
        background-color: #dc3545;
    }

    .timeline-stage {
        text-align: center;
        width: 20%;
        position: relative;
        z-index: 3;
    }

    .stage-dot {
        width: 16px;
        height: 16px;
        margin: 0 auto 8px;
        border-radius: 50%;
        background-color: #dee2e6;
    }

    .stage-dot.filled {
        background-color: #28a745;
    }

    .stage-dot.rejected {
        background-color: #dc3545;
    }

    .stage-label {
        font-size: 0.85rem;
        font-weight: 500;
        color: #495057;
    }

    .stage-label.rejected {
        color: #dc3545;
        font-weight: 600;
    }

    .stage-date {
        font-size: 0.8rem;
        color: #6c757d;
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
                        <div class="order-number">Order #{{ $order->id }}</div>
                        <div class="order-status status-{{ $order->status }}">{{ ucfirst($order->status) }}</div>
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
                            <div class="info-row"><div class="info-label">Delivery Date</div><div class="info-value">{{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : 'Not specified' }}</div></div>
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
                    @if(!in_array($order->status, ['pending', 'rejected', 'cancelled']))
                        <a href="{{ route('franchisee.orders.invoice', $order->id) }}" class="btn btn-primary action-btn" target="_blank">
                            <i class="fas fa-file-pdf me-2"></i> Download Invoice
                        </a>
                    @else
                        <button class="btn btn-outline-secondary action-btn" disabled>
                            <i class="fas fa-file-pdf me-2"></i> Download Invoice
                        </button>
                        @if($order->status === 'pending')
                            <small class="text-muted ms-2 d-flex align-items-center">
                                <i class="fas fa-info-circle me-1"></i> Invoice will be available once approved
                            </small>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Status Card -->
        <div class="col-lg-4">
            <div class="card order-details-card">
                <div class="card-header"><h5 class="card-title mb-0">Order Status</h5></div>
                <div class="card-body">
                    @if($order->status == 'rejected')
                    <!-- Rejected Order Progress -->
                    <div class="progress-track">
                        <div class="progress-line"></div>
                        <div class="progress-line-filled rejected" style="width: 100%;"></div>

                        <!-- Pending Stage -->
                        <div class="timeline-stage">
                            <div class="stage-dot rejected"></div>
                            <div class="stage-label rejected">Pending</div>
                            <div class="stage-date">{{ $order->created_at->format('M j, Y g:i A') }}</div>
                        </div>

                        <!-- Rejected Stage -->
                        <div class="timeline-stage">
                            <div class="stage-dot rejected"></div>
                            <div class="stage-label rejected">Rejected</div>
                            <div class="stage-date">{{ $order->updated_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>
                    @else
                    <!-- Normal Order Progress -->
                    @php
                        $statuses = ['pending', 'approved', 'packed', 'shipped', 'delivered'];
                        $currentIndex = array_search($order->status, $statuses);
                        $dates = [
                            'pending' => $order->created_at,
                            'approved' => $order->status != 'pending' ? $order->updated_at : null,
                            'packed' => in_array($order->status, ['packed', 'shipped', 'delivered']) ? $order->updated_at : null,
                            'shipped' => in_array($order->status, ['shipped', 'delivered']) ? $order->updated_at : null,
                            'delivered' => $order->status == 'delivered' ? $order->updated_at : null
                        ];
                    @endphp

                    <div class="progress-track">
                        <div class="progress-line"></div>
                        <div class="progress-line-filled" style="width: {{ ($currentIndex / (count($statuses) - 1)) * 100 }}%;"></div>

                        @foreach($statuses as $index => $status)
                            <div class="timeline-stage">
                                <div class="stage-dot {{ $index <= $currentIndex ? 'filled' : '' }}"></div>
                                <div class="stage-label">{{ ucfirst($status) }}</div>
                                <div class="stage-date">{{ $dates[$status]?->format('M j, Y g:i A') ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                    @endif
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
                    <p><i class="fas fa-truck text-primary me-2"></i> Estimated delivery:
                        {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : '3-5 business days' }}
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