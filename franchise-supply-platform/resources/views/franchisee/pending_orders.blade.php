@extends('layouts.franchisee')

@section('title', 'Pending Orders - Franchisee Portal')

@section('page-title', 'Pending Orders')

@section('styles')
<style>
    /* Order card styling */
    .order-card {
        transition: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .order-card:hover {
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
        transform: translateY(-5px);
    }
    
    /* Status badge styling */
    .status-badge {
        min-width: 120px;
        display: inline-block;
        text-align: center;
        font-weight: 500;
    }
    
    /* Order progress tracker styling */
    .order-tracker {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        margin: 30px 0;
        padding: 0 10px;
    }
    
    .order-tracker:before {
        content: '';
        position: absolute;
        background: #e5e5e5;
        height: 4px;
        width: 100%;
        top: 50%;
        transform: translateY(-50%);
        left: 0;
        z-index: 1;
    }
    
    .tracker-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 2;
        width: 25%;
        margin-top: 35px;
    }
    
    .step-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #e5e5e5;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        color: #888;
        font-size: 20px;
    }
    
    .step-label {
        font-size: 14px;
        font-weight: 500;
        color: #888;
        margin-top: 5px;
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
        top: 50%;
        transform: translateY(-50%);
        height: 4px;
        background: #4CAF50;
        z-index: 1;
        transition: width 0.5s ease;
        left: 0;
    }
    
    /* Products table styling */
    .products-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 20px;
    }
    
    .products-table th {
        background-color: #f8f9fa;
        padding: 12px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }
    
    .products-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .products-table tr:last-child td {
        border-bottom: none;
    }
    
    .products-table tr:hover {
        background-color: #f8f9fa;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    
    .product-name {
        font-weight: 500;
        color: #212529;
        margin-bottom: 5px;
    }
    
    .product-variant {
        font-size: 13px;
        color: #6c757d;
    }
    
    /* Summary stats styling */
    .stat-card {
        padding: 10px;
        border-radius: 8px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 14px;
        color: #6c757d;
    }
    
    /* Action buttons */
    .action-btn {
        padding: 6px 12px;
        border-radius: 4px;
        transition: all 0.2s;
        font-weight: 500;
        font-size: 14px;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<!-- Order Status Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-md-3 stat-card border-end">
                        <div class="stat-number text-dark">{{ $order_counts['pending'] ?? 0 }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="col-md-3 stat-card border-end">
                        <div class="stat-number text-warning">{{ $order_counts['processing'] ?? 0 }}</div>
                        <div class="stat-label">Processing</div>
                    </div>
                    <div class="col-md-3 stat-card border-end">
                        <div class="stat-number text-primary">{{ $order_counts['shipped'] ?? 0 }}</div>
                        <div class="stat-label">Shipped</div>
                    </div>
                    <div class="col-md-3 stat-card">
                        <div class="stat-number text-success">{{ $order_counts['out_for_delivery'] ?? 0 }}</div>
                        <div class="stat-label">Out for Delivery</div>
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
            <div class="card mb-4 order-card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">Order #{{ $order->order_number }}</h5>
                        <small class="text-muted">Placed on {{ $order->created_at->format('M d, Y, h:i A') }}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="status-badge me-3">
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info px-3 py-2">Processing</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary px-3 py-2">Shipped</span>
                            @elseif($order->status == 'out_for_delivery')
                                <span class="badge bg-success px-3 py-2">Out for Delivery</span>
                            @elseif($order->status == 'delivered')
                                <span class="badge bg-success px-3 py-2">Delivered</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger px-3 py-2">Cancelled</span>
                            @endif
                        </span>
                        <a href="{{ route('franchisee.orders.details', $order->id) }}" class="btn btn-sm btn-outline-success action-btn">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Order Tracker - Improved version -->
                    <div class="position-relative">
                        <div class="order-tracker">
                            <!-- Progress line that fills based on order status -->
                            @php
                                $progressWidth = 0;
                                if($order->status == 'pending') $progressWidth = 0;
                                elseif($order->status == 'processing') $progressWidth = 33;
                                elseif($order->status == 'shipped') $progressWidth = 66;
                                elseif(in_array($order->status, ['out_for_delivery', 'delivered'])) $progressWidth = 100;
                            @endphp
                            <div class="progress-line" style="width: {{ $progressWidth }}%;"></div>
                            
                            <!-- Step 1: Pending -->
                            <div class="tracker-step {{ in_array($order->status, ['pending', 'processing', 'shipped', 'out_for_delivery', 'delivered']) ? 'active' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="step-label">Pending</div>
                            </div>
                            
                            <!-- Step 2: Processing -->
                            <div class="tracker-step {{ in_array($order->status, ['processing', 'shipped', 'out_for_delivery', 'delivered']) ? 'active' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="step-label">Processing</div>
                            </div>
                            
                            <!-- Step 3: Shipped -->
                            <div class="tracker-step {{ in_array($order->status, ['shipped', 'out_for_delivery', 'delivered']) ? 'active' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="step-label">Shipped</div>
                            </div>
                            
                            <!-- Step 4: Delivered -->
                            <div class="tracker-step {{ in_array($order->status, ['delivered']) ? 'active' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="step-label">Delivered</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="row align-items-center mt-4">
                        <div class="col-md-6">
                            <div class="d-flex">
                                <div class="me-4">
                                    <p class="mb-1 text-muted">Total Items</p>
                                    <h5>{{ $order->items->sum('quantity') }}</h5>
                                </div>
                                <div class="me-4">
                                    <p class="mb-1 text-muted">Total Amount</p>
                                    <h5>${{ number_format($order->total_amount, 2) }}</h5>
                                </div>
                                @if($order->tracking_number)
                                <div>
                                    <p class="mb-1 text-muted">Tracking</p>
                                    <h5>
                                        <a href="{{ route('franchisee.track', $order->tracking_number) }}" class="link-primary">
                                            {{ $order->tracking_number }}
                                        </a>
                                    </h5>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <p class="mb-1 text-muted">Estimated Delivery</p>
                                    <h5>{{ $order->estimated_delivery ? $order->estimated_delivery->format('M d, Y') : 'Not available' }}</h5>
                                </div>
                                <div>
                                    <p class="mb-1 text-muted">Shipping Address</p>
                                    <h5>{{ $order->shipping_address ?? '478 Mortimer Ave' }}</h5>
                                </div>
                            </div>
                            
                            @if($order->status == 'pending')
                            <div class="d-flex mt-3 justify-content-end">
                                <form action="{{ route('franchisee.orders.cancel', $order->id) }}" method="POST" class="me-2" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger action-btn">
                                        <i class="fas fa-times me-1"></i> Cancel Order
                                    </button>
                                </form>
                                <a href="{{ route('franchisee.orders.modify', $order->id) }}" class="btn btn-outline-primary action-btn">
                                    <i class="fas fa-edit me-1"></i> Modify Order
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Order Items Table -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Order Items</h6>
                        <div class="table-responsive">
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th style="width: 80px">Image</th>
                                        <th>Product</th>
                                        <th style="width: 100px">Price</th>
                                        <th style="width: 100px">Quantity</th>
                                        <th style="width: 100px" class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                    <tr>
                                    <td>
                                      <img src="{{ $item->product->images && $item->product->images->count() > 0 ? asset('storage/' . $item->product->images->first()->image_url) : asset('images/placeholder-product.jpg') }}" 
                                           alt="{{ $item->product->name }}" class="product-image">
                                    </td>
                                        <td>
                                            <div class="product-name">{{ $item->product->name }}</div>
                                            @if($item->variant)
                                            <div class="product-variant">{{ $item->variant->name }}</div>
                                            @endif
                                        </td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold">${{ number_format($order->total_amount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                @if($order->notes)
                <div class="card-footer bg-light">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-sticky-note text-warning me-2"></i>
                        <div>
                            <strong>Order Notes:</strong> {{ $order->notes }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
@endif
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endsection