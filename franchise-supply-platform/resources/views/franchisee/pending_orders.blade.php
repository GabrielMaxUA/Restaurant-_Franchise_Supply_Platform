@extends('layouts.franchisee')

@section('title', 'Pending Orders - Franchisee Portal')

@section('page-title', 'Pending Orders')

@section('styles')
<style>
  /* Order Summary styles */
.order-summary-section {
    margin-bottom: 2rem;
}

.summary-item {
    min-width: 150px;
}

.summary-label {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
    font-size: 14px;
}

.summary-value {
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

@media (max-width: 767px) {
    .col-md-6.border-end {
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
}
    /* Order card styling */
    .order-card {
      translate: all 0.3s ease;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .order-card:hover {
        translate: background-color:rgb(177, 178, 179);
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
        border-radius: 2px;
    }
    
    .tracker-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        z-index: 3; /* Increased z-index to appear above the progress line */
        width: 20%; /* 20% for 5 equal steps */
        margin-top: 35px;
    }
    
    .step-icon {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 50px; /* Reduced from 60px for better spacing */
        height: 50px; /* Reduced from 60px for better spacing */
        border-radius: 50%;
        background: #fff;
        border: 3px solid #e5e5e5;
        margin-bottom: 10px;
        transition: all 0.3s ease;
        color: #888;
        font-size: 18px; /* Slightly smaller icons */
        position: relative;
        z-index: 3; /* Keep above the progress line */
    }
    
    .step-label {
        font-size: 13px; /* Slightly smaller text */
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
        transition: width 0.5s ease, background-color 0.5s ease;
        left: 0;
        border-radius: 2px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
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
        margin: 10px;
        padding: 10px;
        width: calc(100% / 4 - 40px);
        border-radius: 8px;
        border: 1px solid #e5e5e5;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer; /* Added cursor pointer to show it's clickable */
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
    }
    
    /* Style for active stat card */
    .stat-card.active {
        background-color: #f8f9fa;
        border: 2px solid #4CAF50;
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

    /* Rejected order action buttons container */
    .rejected-actions {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    .rejected-actions .btn {
        min-width: 160px;
        padding: 10px 20px;
        font-weight: 500;
    }

    /* Mobile responsiveness for tracker */
    @media (max-width: 767px) {
        .order-tracker {
            overflow-x: auto;
            justify-content: flex-start;
            padding-bottom: 15px;
        }
        
        .tracker-step {
            min-width: 100px;
            width: auto;
            margin-right: 30px;
        }
    }
    
    /* Filter indicator */
    .filter-indicator {
        display: inline-block;
        padding: 6px 12px;
        background-color: #f8f9fa;
        border-radius: 20px;
        margin-bottom: 15px;
        font-size: 14px;
        color: #495057;
    }
    
    .clear-filter {
        cursor: pointer;
        color: #dc3545;
        margin-left: 8px;
    }
    
    .clear-filter:hover {
        text-decoration: underline;
    }

    .card {
      border: none;
    }

    .alert-info{
      height: auto;
      padding: 15px;
    }

    .alert-info > a{
      font-size: 1.3em;
      }
</style>
@endsection

@section('content')
<!-- Order Status Summary -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="row g-0 border-0 d-flex justify-content-center">
                    <div class="col-md-3 stat-card status-filter {{ request('status') == 'pending' ? 'active' : '' }}" data-status="pending">
                        <div class="stat-number text-dark">{{ $order_counts['pending'] ?? 0 }}</div>
                        <div class="stat-label">Pending</div>
                    </div>
                    <div class="col-md-3 stat-card status-filter {{ request('status') == 'processing' ? 'active' : '' }}" data-status="processing">
                        <div class="stat-number text-info">{{ $order_counts['processing'] ?? 0 }}</div>
                        <div class="stat-label">Processing</div>
                    </div>
                    <div class="col-md-3 stat-card status-filter {{ request('status') == 'packed' ? 'active' : '' }}" data-status="packed">
                        <div class="stat-number text-secondary">{{ $order_counts['packed'] ?? 0 }}</div>
                        <div class="stat-label">Packed</div>
                    </div>
                    <div class="col-md-3 stat-card status-filter {{ request('status') == 'shipped' ? 'active' : '' }}" data-status="shipped">
                        <div class="stat-number text-primary">{{ $order_counts['shipped'] ?? 0 }}</div>
                        <div class="stat-label">Shipped</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Indicator -->
@if(request('status'))
<div class="filter-indicator">
    <i class="fas fa-filter me-2"></i> Filtered by: <strong>{{ ucfirst(request('status')) }}</strong>
    <span class="clear-filter" id="clearFilter"><i class="fas fa-times-circle"></i> Clear</span>
</div>
@endif

@if($orders->isEmpty())
    <div class="alert-info">
        <i class="fas fa-info-circle me-2"></i> 
        @if(request('status'))
            No orders with status "{{ ucfirst(request('status')) }}" found.
        @else
            You don't have any pending orders at the moment.
        @endif
        <a href="{{ route('franchisee.catalog') }}" >Browse products</a> to place an order.
    </div>
@else
    <!-- Orders list -->
    <div class="row">
        <div class="col-md-12">
            @foreach($orders as $order)
            <div class="card mb-4 order-card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="mb-0 fw-bold">Order # {{ $order->order_number ?? $order->id }}</h5>
                        <small class="text-muted">Placed on {{ $order->created_at->format('M d, Y, h:i A') }}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="status-badge me-3">
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark px-3 py-2">Pending</span>
                            @elseif($order->status == 'processing')
                                <span class="badge bg-info px-3 py-2">Processing</span>
                            @elseif($order->status == 'packed')
                                <span class="badge bg-secondary px-3 py-2">Packed</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-primary px-3 py-2">Shipped</span>
                            @endif
                        </span>
                        <a href="{{ route('franchisee.orders.details', $order->id) }}" class="btn btn-sm btn-outline-success action-btn">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Regular Order Tracker -->
                    <div class="position-relative">
                        <div class="order-tracker">
                            <!-- Progress line that fills based on order status -->
                            @php
                                // Calculate progress width based on order status
                                $progressWidth = 0;
                                $progressColor = '#4CAF50'; // Default green
                                $numSteps = 5; // Total number of steps in the progress bar

                                // Calculate position for each status (as percentage)
                                $stepWidth = 100 / ($numSteps - 1); // Width for each step (0%, 25%, 50%, 75%, 100%)

                                if($order->status == 'pending') {
                                    $progressWidth = $stepWidth * 0; // 0%
                                }
                                elseif($order->status == 'processing' || $order->status == 'approved') {
                                    $progressWidth = $stepWidth * 1; // 25%
                                }
                                elseif($order->status == 'packed') {
                                    $progressWidth = $stepWidth * 2; // 50%
                                }
                                elseif($order->status == 'shipped') {
                                    $progressWidth = $stepWidth * 3; // 75%
                                }
                                elseif($order->status == 'delivered') {
                                    $progressWidth = $stepWidth * 4; // 100%
                                }
                                elseif($order->status == 'rejected' || $order->status == 'cancelled') {
                                    $progressWidth = $stepWidth * 1; // 25% (at the approved stage)
                                    $progressColor = '#dc3545'; // Red for rejected/cancelled
                                }
                            @endphp
                            <div class="progress-line" style="width: {{ $progressWidth }}%; background-color: {{ $progressColor }}; z-index: 2;"></div>
                            
                            <!-- Step 1: Pending -->
                            <div class="tracker-step {{ $order->status == 'pending' ? 'active' : ($order->status == 'rejected' || $order->status == 'cancelled' ? '' : 'completed') }}">
                                <div class="step-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                <div class="step-label">Pending</div>
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
                            </div>

                            <!-- Step 3: Packed -->
                            <div class="tracker-step {{ $order->status == 'packed' ? 'active' :
                                        (in_array($order->status, ['shipped', 'delivered']) ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="step-label">Packed</div>
                            </div>

                            <!-- Step 4: Shipped -->
                            <div class="tracker-step {{ $order->status == 'shipped' ? 'active' :
                                        ($order->status == 'delivered' ? 'completed' : '') }}">
                                <div class="step-icon">
                                    <i class="fas fa-shipping-fast"></i>
                                </div>
                                <div class="step-label">Shipped</div>
                            </div>

                            <!-- Step 5: Delivered -->
                            <div class="tracker-step {{ $order->status == 'delivered' ? 'active' : '' }}">
                                <div class="step-icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="step-label">Delivered</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="order-summary-section mt-4 mb-4">
                        <h6 class="fw-bold mb-3">Order Summary</h6>
                        <div class="card border-0">
                            <div class="card-body">
                                <div class="row ">
                                    <!-- Left column - Order stats -->
                                    <div class="col-md-6 border-end">
                                        <div class="d-flex flex-wrap ">
                                            <div class="summary-item me-4 mb-3">
                                                <div class="summary-label">
                                                    <i class="fas fa-box-open text-primary me-2"></i>
                                                    <span class="text-muted">Total Items</span>
                                                </div>
                                                <div class="summary-value">{{ $order->items->sum('quantity') }}</div>
                                            </div>
                                            
                                            <div class="summary-item me-4 mb-3">
                                                <div class="summary-label">
                                                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                                                    <span class="text-muted">Total Amount</span>
                                                </div>
                                                <div class="summary-value">${{ number_format($order->total_amount, 2) }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right column - Shipping info -->
                                    <div class="col-md-6">
                                        <div class="d-flex flex-column">
                                            <div class="summary-item mb-3">
                                                <div class="summary-label">
                                                    <i class="fas fa-calendar-alt text-danger me-2"></i>
                                                    <span class="text-muted">Estimated Delivery</span>
                                                </div>
                                                <div class="summary-value">
                                                    {{ $order->estimated_delivery ? $order->estimated_delivery->format('M d, Y') : 'Not available' }}
                                                </div>
                                            </div>
                                            
                                            <div class="summary-item mb-3">
                                                <div class="summary-label">
                                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                                    <span class="text-muted">Shipping Address</span>
                                                </div>
                                                <div class="summary-value">
                                                    {{ $order->shipping_address ?? '478 Mortimer Ave' }}
                                                    @if($order->shipping_city && $order->shipping_state)
                                                        <div class="text-muted small">{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($order->delivery_preference)
                                            <div class="summary-item">
                                                <div class="summary-label">
                                                    <i class="fas fa-shipping-fast text-success me-2"></i>
                                                    <span class="text-muted">Delivery Method</span>
                                                </div>
                                                <div class="summary-value">
                                                    @if($order->delivery_preference == 'standard')
                                                        Standard Delivery
                                                    @elseif($order->delivery_preference == 'express')
                                                        Express Delivery
                                                    @elseif($order->delivery_preference == 'scheduled')
                                                        Scheduled Delivery
                                                    @else
                                                        {{ ucfirst($order->delivery_preference) }}
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                {{ $orders->appends(request()->query())->links() }}
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
        
        // Add click event to status filter cards
        const statusFilters = document.querySelectorAll('.status-filter');
        statusFilters.forEach(card => {
            card.addEventListener('click', function() {
                const status = this.getAttribute('data-status');
                window.location.href = `{{ route('franchisee.orders.pending') }}?status=${status}`;
            });
        });
        
        // Add click event to clear filter
        const clearFilter = document.getElementById('clearFilter');
        if (clearFilter) {
            clearFilter.addEventListener('click', function() {
                window.location.href = '{{ route('franchisee.orders.pending') }}';
            });
        }
    });
</script>
@endsection