@extends('layouts.franchisee')

@section('title', 'Pending Orders - Franchisee Portal')

@section('page-title', 'Pending Orders')

@section('styles')
<style>
    .timeline-steps {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
    }
    
    .timeline-steps:before {
        content: '';
        position: absolute;
        background: #e5e5e5;
        height: 3px;
        width: 100%;
        top: 25px;
        z-index: 0;
    }
    
    .timeline-step {
        position: relative;
        z-index: 1;
        text-align: center;
        width: 20%;
    }
    
    .timeline-step-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #e5e5e5;
        margin: 0 auto 10px;
        color: #aaa;
    }
    
    .timeline-step.active .timeline-step-icon {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }
    
    .timeline-step.completed .timeline-step-icon {
        background: #28a745;
        border-color: #28a745;
        color: white;
    }
    
    .timeline-steps .timeline-step:nth-child(1) ~ .timeline-step:before {
        content: '';
        position: absolute;
        background: #e5e5e5;
        height: 3px;
        top: 25px;
        left: -50%;
        width: 100%;
        z-index: -1;
    }
    
    .timeline-steps .timeline-step.completed:before {
        background: #28a745;
    }
    
    .order-item {
        transition: all 0.3s;
    }
    
    .order-item:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }
    
    .status-label {
        width: 120px;
    }
</style>
@endsection

@section('content')
<!-- Order Status Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center border-end">
                        <h3>{{ $order_counts['total'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Total Pending Orders</p>
                    </div>
                    <div class="col-md-3 text-center border-end">
                        <h3 class="text-warning">{{ $order_counts['processing'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Processing</p>
                    </div>
                    <div class="col-md-3 text-center border-end">
                        <h3 class="text-primary">{{ $order_counts['shipped'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Shipped</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h3 class="text-success">{{ $order_counts['out_for_delivery'] ?? 0 }}</h3>
                        <p class="text-muted mb-0">Out for Delivery</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($orders->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> You don't have any pending orders at the moment.
        <a href="{{ route('franchisee.catalog') }}" class="alert-link">Browse products</a> to place an order.
    </div>
@else
    <!-- Orders list -->
    <div class="row">
        <div class="col-md-12">
            @foreach($orders as $order)
            <div class="card mb-4 order-item">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Order #{{ $order->order_number }}</h5>
                        <small class="text-muted">Placed on {{ $order->created_at->format('M d, Y, h:i A') }}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="status-label">
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info">Processing</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary">Shipped</span>
                            @elseif($order->status == 'out_for_delivery')
                                <span class="badge bg-success">Out for Delivery</span>
                            @elseif($order->status == 'delivered')
                                <span class="badge bg-success">Delivered</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @endif
                        </span>
                        <a href="{{ route('franchisee.orders.details', $order->id) }}" class="btn btn-sm btn-outline-success ms-2">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Order Timeline -->
                    <div class="timeline-steps mb-4">
                        <div class="timeline-step {{ in_array($order->status, ['pending', 'processing', 'shipped', 'out_for_delivery', 'delivered']) ? 'completed' : '' }}">
                            <div class="timeline-step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <p class="text-muted mb-0">Confirmed</p>
                        </div>
                        <div class="timeline-step {{ in_array($order->status, ['processing', 'shipped', 'out_for_delivery', 'delivered']) ? 'completed' : '' }}">
                            <div class="timeline-step-icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <p class="text-muted mb-0">Processing</p>
                        </div>
                        <div class="timeline-step {{ in_array($order->status, ['shipped', 'out_for_delivery', 'delivered']) ? 'completed' : '' }}">
                            <div class="timeline-step-icon">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                            <p class="text-muted mb-0">Shipped</p>
                        </div>
                        <div class="timeline-step {{ in_array($order->status, ['out_for_delivery', 'delivered']) ? 'completed' : '' }}">
                            <div class="timeline-step-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <p class="text-muted mb-0">Out for Delivery</p>
                        </div>
                        <div class="timeline-step {{ $order->status == 'delivered' ? 'completed' : '' }}">
                            <div class="timeline-step-icon">
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                            <p class="text-muted mb-0">Delivered</p>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Items:</strong> {{ $order->items_count }} items</p>
                            <p class="mb-2"><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
                            @if($order->tracking_number)
                            <p class="mb-0">
                                <strong>Tracking:</strong> 
                                <a href="{{ route('franchisee.track', $order->tracking_number) }}" class="link-primary">
                                    {{ $order->tracking_number }}
                                </a>
                            </p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Estimated Delivery:</strong> {{ $order->estimated_delivery ? $order->estimated_delivery->format('M d, Y') : 'Not available' }}</p>
                            <p class="mb-2"><strong>Shipping Address:</strong> {{ $order->shipping_address }}</p>
                            
                            @if($order->status == 'pending')
                            <div class="d-flex mt-3">
                                <form action="{{ route('franchisee.orders.cancel', $order->id) }}" method="POST" class="me-2" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times me-1"></i> Cancel Order
                                    </button>
                                </form>
                                <a href="{{ route('franchisee.orders.modify', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i> Modify Order
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Order Items Snapshot (just showing quantity) -->
                    <div class="mt-4">
                        <h6>Order Items:</h6>
                        <div class="row">
                            @foreach($order->items as $item)
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-2" style="width: 40px; height: 40px; overflow: hidden;">
                                        <img src="{{ $item->product->image_url ?? asset('images/placeholder-product-small.jpg') }}" 
                                             class="img-fluid rounded" alt="{{ $item->product->name }}">
                                    </div>
                                    <div>
                                        <p class="mb-0 fw-medium">{{ $item->product->name }}</p>
                                        <small class="text-muted">{{ $item->quantity }} × ${{ number_format($item->price, 2) }}</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                @if($order->notes)
                <div class="card-footer bg-light">
                    <small><strong>Order Notes:</strong> {{ $order->notes }}</small>
                </div>
                @endif
            </div>
            @endforeach
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    // Nothing special needed for this page
</script>
@endsection