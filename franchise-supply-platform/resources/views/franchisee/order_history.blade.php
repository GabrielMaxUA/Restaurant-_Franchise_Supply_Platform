@extends('layouts.franchisee')

@section('title', 'Order History - Franchisee Portal')

@section('page-title', 'Order History')

@section('styles')
<style>
    .order-card {
        transition: all 0.2s ease;
    }
    
    .order-card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .filter-section {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .status-badge {
        width: 100px;
        display: inline-block;
        text-align: center;
    }
    
    .order-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .order-summary-item {
        flex: 1;
        min-width: 120px;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        text-align: center;
    }
    
    .repeat-order-btn {
        transition: all 0.2s;
    }
    
    .repeat-order-btn:hover {
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<!-- Filter Section -->
<div class="filter-section mb-4">
    <form action="{{ route('franchisee.orders.history') }}" method="GET">
        <div class="row align-items-end">
            <div class="col-md-3 mb-3 mb-md-0">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select" id="sort_by" name="sort_by">
                    <option value="date_desc" {{ request('sort_by') == 'date_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="date_asc" {{ request('sort_by') == 'date_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="total_desc" {{ request('sort_by') == 'total_desc' ? 'selected' : '' }}>Highest Total</option>
                    <option value="total_asc" {{ request('sort_by') == 'total_asc' ? 'selected' : '' }}>Lowest Total</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-success w-100">Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 col-6 border-end">
                        <h5>{{ $stats['total_orders'] ?? 0 }}</h5>
                        <p class="text-muted mb-0">Total Orders</p>
                    </div>
                    <div class="col-md-3 col-6 border-end">
                        <h5>${{ number_format($stats['total_spent'] ?? 0, 2) }}</h5>
                        <p class="text-muted mb-0">Total Spent</p>
                    </div>
                    <div class="col-md-3 col-6 border-end">
                        <h5>{{ $stats['total_items'] ?? 0 }}</h5>
                        <p class="text-muted mb-0">Items Ordered</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <h5>{{ $stats['avg_order_value'] ? '$' . number_format($stats['avg_order_value'], 2) : '$0.00' }}</h5>
                        <p class="text-muted mb-0">Avg. Order Value</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
@if($orders->isEmpty())
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i> No order history found for the selected criteria.
    </div>
@else
    <div class="row">
        <div class="col-md-12">
            <div class="list-group">
                @foreach($orders as $order)
                <div class="list-group-item p-0 mb-3 border rounded order-card">
                    <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                        <div>
                            <h5 class="mb-1">Order #{{ $order->order_number }}</h5>
                            <p class="text-muted mb-0">
                                <i class="far fa-calendar-alt me-1"></i> {{ $order->created_at->format('M d, Y') }}
                                <span class="mx-2">|</span>
                                <i class="far fa-clock me-1"></i> {{ $order->created_at->format('h:i A') }}
                            </p>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="status-badge me-3">
                                @if($order->status == 'delivered')
                                    <span class="badge bg-success">Delivered</span>
                                @elseif($order->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </span>
                            
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="orderActions{{ $order->id }}" data-bs-toggle="dropdown" aria-expanded="false">
                                    Actions
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="orderActions{{ $order->id }}">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('franchisee.orders.details', $order->id) }}">
                                            <i class="fas fa-eye me-2"></i> View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('franchisee.orders.invoice', $order->id) }}">
                                            <i class="fas fa-file-invoice me-2"></i> Download Invoice
                                        </a>
                                    </li>
                                    @if($order->status == 'delivered')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('franchisee.orders.repeat', $order->id) }}">
                                            <i class="fas fa-sync-alt me-2"></i> Repeat Order
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="mb-2">Order Summary</h6>
                                    <div class="order-summary">
                                        <div class="order-summary-item">
                                            <small class="text-muted">Items</small>
                                            <h6 class="mb-0">{{ $order->total_items }}</h6>
                                        </div>
                                        <div class="order-summary-item">
                                            <small class="text-muted">Total</small>
                                            <h6 class="mb-0">${{ number_format($order->total, 2) }}</h6>
                                        </div>
                                        <div class="order-summary-item">
                                            <small class="text-muted">Payment</small>
                                            <h6 class="mb-0">{{ ucfirst($order->payment_method) }}</h6>
                                        </div>
                                    </div>
                                </div>
                                
                                @if($order->delivered_at)
                                <p class="mb-0 small">
                                    <i class="fas fa-truck me-1 text-success"></i> 
                                    Delivered on {{ $order->delivered_at->format('M d, Y') }}
                                </p>
                                @elseif($order->cancelled_at)
                                <p class="mb-0 small">
                                    <i class="fas fa-ban me-1 text-danger"></i> 
                                    Cancelled on {{ $order->cancelled_at->format('M d, Y') }}
                                </p>
                                @endif
                            </div>
                            
                            <div class="col-md-6">
                                <h6 class="mb-2">Items Preview</h6>
                                <div class="row g-2">
                                    @foreach($order->items->take(4) as $item)
                                    <div class="col-3">
                                        <div class="position-relative">
                                            <img src="{{ $item->product->image_url ?? asset('images/placeholder-product-small.jpg') }}" 
                                                class="img-fluid rounded" alt="{{ $item->product->name }}" 
                                                data-bs-toggle="tooltip" title="{{ $item->product->name }} ({{ $item->quantity }}x)">
                                            <span class="position-absolute top-0 end-0 badge rounded-pill bg-secondary">
                                                {{ $item->quantity }}
                                            </span>
                                        </div>
                                    </div>
                                    @endforeach
                                    
                                    @if(count($order->items) > 4)
                                    <div class="col-3">
                                        <div class="d-flex justify-content-center align-items-center bg-light rounded h-100">
                                            <span class="text-muted">+{{ count($order->items) - 4 }} more</span>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                
                                @if($order->status == 'delivered')
                                <div class="text-end mt-3">
                                    <a href="{{ route('franchisee.orders.repeat', $order->id) }}" class="btn btn-sm btn-success repeat-order-btn">
                                        <i class="fas fa-sync-alt me-1"></i> Repeat Order
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
@endif

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="row">
                    <div class="col-md-4">
                        <a href="{{ route('franchisee.orders.reports') }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-chart-bar me-2"></i> Generate Order Reports
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('franchisee.orders.export') }}" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-file-excel me-2"></i> Export Order History
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('franchisee.catalog') }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-shopping-basket me-2"></i> Place New Order
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    
    // Date range validation
    const dateFromInput = document.getElementById('date_from');
    const dateToInput = document.getElementById('date_to');
    
    if (dateFromInput && dateToInput) {
        dateFromInput.addEventListener('change', function() {
            dateToInput.min = this.value;
            if (dateToInput.value && dateToInput.value < this.value) {
                dateToInput.value = this.value;
            }
        });
        
        dateToInput.addEventListener('change', function() {
            dateFromInput.max = this.value;
            if (dateFromInput.value && dateFromInput.value > this.value) {
                dateFromInput.value = this.value;
            }
        });
    }
</script>