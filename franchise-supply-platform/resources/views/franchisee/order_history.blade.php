\@extends('layouts.franchisee')

@section('title', 'Order History - Franchisee Portal')

@section('page-title', 'Order History')

@section('styles')
    <link href="{{ asset('css/status-styles.css') }}" rel="stylesheet">
<style>
    .order-card {
        transition: all 0.2s ease;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .order-card:hover {
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
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
    
    .order-summary-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 1rem;
    }
    
    .order-summary-table th {
        background-color: #f8f9fa;
        padding: 10px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
        color: #495057;
        border-bottom: 1px solid #dee2e6;
    }
    
    .order-summary-table td {
        padding: 10px 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .order-summary-table tr:last-child td {
        border-bottom: none;
    }
    
    .items-preview {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .item-preview-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #dee2e6;
    }
    
    .item-preview-count {
        position: absolute;
        top: -5px;
        right: -5px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        background-color: #6c757d;
        color: white;
        border-radius: 50%;
        font-size: 12px;
    }
    
    .repeat-order-btn {
        transition: all 0.2s;
    }
    
    .repeat-order-btn:hover {
        transform: scale(1.05);
    }
    
    .date-info {
        font-size: 13px;
        color: #6c757d;
    }
    
    .action-dropdown .dropdown-item {
        padding: 8px 16px;
        font-size: 14px;
    }
    
    .action-dropdown .dropdown-item i {
        width: 18px;
    }
    
</style>
@endsection

@section('content')
<!-- Filter Section -->
<div class="filter-section mb-4">
    <form id="filterForm" action="{{ route('franchisee.orders.history') }}" method="GET">
        <div class="row align-items-end">
            <div class="col-md-3 mb-3 mb-md-0">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" class="form-control filter-input" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" class="form-control filter-input" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label for="status" class="form-label">Status</label>
                <select class="form-select filter-input" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2 mb-3 mb-md-0">
                <label for="sort_by" class="form-label">Sort By</label>
                <select class="form-select filter-input" id="sort_by" name="sort_by">
                    <option value="date_desc" {{ request('sort_by', 'date_desc') == 'date_desc' ? 'selected' : '' }}>Newest First</option>
                    <option value="date_asc" {{ request('sort_by') == 'date_asc' ? 'selected' : '' }}>Oldest First</option>
                    <option value="total_desc" {{ request('sort_by') == 'total_desc' ? 'selected' : '' }}>Highest Total</option>
                    <option value="total_asc" {{ request('sort_by') == 'total_asc' ? 'selected' : '' }}>Lowest Total</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" id="resetFilters" class="btn btn-outline-secondary w-100 mb-2">Reset Filters</button>
                <button type="submit" class="btn btn-success w-100">Apply Filters</button>
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
            <div class="table-responsive">
                <table class="table align-middle border">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 80px">Image</th>
                            <th>Order Details</th>
                            <th style="width: 120px" class="text-center">Total</th>
                            <th style="width: 180px" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                @if($order->items && $order->items->count() > 0)
                                    <img src="{{ $order->items->first()->product->images && $order->items->first()->product->images->count() > 0 ? asset('storage/' . $order->items->first()->product->images->first()->image_url) : asset('images/placeholder-product.jpg') }}" 
                                         alt="{{ $order->items->first()->product->name }}" 
                                         class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <img src="{{ asset('images/placeholder-product.jpg') }}" 
                                         alt="No product" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-1">
                                        <strong># {{ $order->order_number ?? $order->id }}</strong>
                                        <span class="mx-2">|</span>
                                        <span class="fw-medium">{{ $order->created_at->format('M d, Y') }}</span>
                                        <span class="mx-2">|</span>
                                        <span>{{ $order->created_at->format('h:i A') }}</span>
                                        <span class="ms-2 text-center">
                                            @if($order->status == 'pending')
                                                <span class="status-badge status-pending">Pending</span>
                                            @elseif($order->status == 'approved' || $order->status == 'processing')
                                                <span class="status-badge status-processing">{{ ucfirst($order->status) }}</span>
                                            @elseif($order->status == 'packed')
                                                <span class="status-badge status-packed">Packed</span>
                                            @elseif($order->status == 'shipped')
                                                <span class="status-badge status-shipped">Shipped</span>
                                            @elseif($order->status == 'delivered')
                                                <span class="status-badge status-delivered">Delivered</span>
                                            @elseif($order->status == 'rejected' || $order->status == 'cancelled')
                                                <span class="status-badge status-rejected">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    
                                    @if($order->items && $order->items->count() > 0)
                                        <div>
                                            @foreach($order->items->take(2) as $item)
                                                <span class="d-block small">{{ $item->product->name }} 
                                                @if($item->variant)
                                                    ({{ $item->variant->name }})
                                                @endif
                                                × {{ $item->quantity }}</span>
                                            @endforeach
                                            
                                            @if($order->items->count() > 2)
                                                <span class="small text-muted">+{{ $order->items->count() - 2 }} more items</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-medium">${{ number_format($order->total_amount, 2) }}</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-end">
                                    <a href="{{ route('franchisee.orders.details', $order->id) }}" class="btn btn-sm btn-outline-primary me-2" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(in_array($order->status, ['approved', 'packed', 'shipped', 'delivered']))
                                    <a href="{{ route('franchisee.orders.invoice', $order->id) }}?print=true" class="btn btn-sm btn-outline-warning me-2" title="View & Print Invoice" target="_blank">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    @endif
                                    <!-- Always show Repeat Order button, regardless of status -->
                                    <a href="{{ route('franchisee.orders.repeat', $order->id) }}" class="btn btn-sm btn-success repeat-order-btn" title="Repeat Order">
                                        <i class="fas fa-sync-alt"></i> Repeat
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                @include('components.pagination', ['items' => $orders])
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
                            <i class="fas fa-chart-bar me-2"></i> View Analytics & Reports
                        </a>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-success w-100 mb-2 dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download me-2"></i> Export Data
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item download-export" href="{{ route('franchisee.orders.export') }}?format=csv{{ request('date_from') ? '&date_from=' . request('date_from') : '' }}{{ request('date_to') ? '&date_to=' . request('date_to') : '' }}{{ request('status') ? '&status=' . request('status') : '' }}">
                                    <i class="fas fa-file-csv me-2"></i> Export as CSV
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item download-export" href="{{ route('franchisee.orders.export') }}?format=csv">
                                    <i class="fas fa-table me-2"></i> Export All Orders
                                </a>
                            </li>
                        </ul>
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
        
        // Date range validation
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        
        if (dateFromInput && dateToInput) {
            dateFromInput.addEventListener('change', function() {
                dateToInput.min = this.value;
                if (dateToInput.value && dateToInput.value < this.value) {
                    dateToInput.value = this.value;
                }
                submitForm();
            });
            
            dateToInput.addEventListener('change', function() {
                dateFromInput.max = this.value;
                if (dateFromInput.value && dateFromInput.value > this.value) {
                    dateFromInput.value = this.value;
                }
                submitForm();
            });
        }
        
        // Live filter updates
        const filterInputs = document.querySelectorAll('.filter-input');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                submitForm();
            });
        });
        
        // Reset filters
        const resetButton = document.getElementById('resetFilters');
        resetButton.addEventListener('click', function() {
            const form = document.getElementById('filterForm');
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                if (input.type === 'date') {
                    input.value = '';
                } else if (input.tagName === 'SELECT') {
                    if (input.id === 'sort_by') {
                        input.value = 'date_desc'; // Default sort to newest first
                    } else {
                        input.value = '';
                    }
                }
            });
            
            submitForm();
        });
        
        // Submit form function
        function submitForm() {
            document.getElementById('filterForm').submit();
        }
        
        // Handle export download links
        document.querySelectorAll('.download-export').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Show loading overlay
                if (typeof window.showLoadingOverlay === 'function') {
                    window.showLoadingOverlay('Preparing export...');
                }
                
                // Create iframe for download
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                document.body.appendChild(iframe);
                iframe.src = this.href;
                
                // Hide loading overlay after delay
                setTimeout(() => {
                    if (typeof window.hideLoadingOverlay === 'function') {
                        window.hideLoadingOverlay();
                    }
                    document.body.removeChild(iframe);
                }, 2000);
            });
        });
    });
</script>
@endsection