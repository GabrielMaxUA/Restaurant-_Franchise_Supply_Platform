@extends('layouts.admin')

@section('title', isset($username) ? "Orders for {$username} - Restaurant Franchise Supply Platform" : 'Orders - Restaurant Franchise Supply Platform')

@section('page-title', $pageTitle ?? 'Order Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
 
    .search-card {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    /* Badge styling for consistent badges */
    .badge.bg-warning.text-dark {
        background-color: #f6c23e !important;
        border: 1px solid #f6c23e;
    }

    .badge.bg-primary {
        background-color: #4e73df !important;
        border: 1px solid #4e73df;
    }

    .badge.bg-info {
        background-color: #36b9cc !important;
        border: 1px solid #36b9cc;
    }

    .badge.bg-success {
        background-color: #1cc88a !important;
        border: 1px solid #1cc88a;
    }

    .badge.bg-danger {
        background-color: #e74a3b !important;
        border: 1px solid #e74a3b;
    }

    .badge.bg-secondary {
        background-color: #858796 !important;
        border: 1px solid #858796;
    }

    

    /* Filter badge styling - intentionally left empty as styling is defined below */
    
    .search-card h5 {
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 0.75rem;
        margin-bottom: 1rem;
    }
    
    .data-table {
        background-color: white;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .data-table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .data-table tbody tr:hover {
        background-color: rgba(0,0,0,.03);
    }
    
    .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .flatpickr-input {
        background-color: white !important;
    }
    
    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    /* Active filter badges styling to match the rest of the admin UI */
    .active-filters .badge {
        display: inline-flex;
        align-items: center;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        color: #495057;
        font-weight: normal;
        padding: 0.4em 0.7em;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
        border-radius: 0.375rem;
    }

    .active-filters .badge-label {
        font-weight: 600;
        margin-right: 0.3rem;
        color: #4e73df;
    }
</style>
@endsection

@section('content')


<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">{{ $pageTitle ?? 'Manage Orders' }}</h1>
    
    @if(isset($username))
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> View All Orders
        </a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="search-card">
    <h5>Search Orders</h5>
    
    <form action="{{ route('admin.orders.index') }}" method="GET" id="orders-filter-form">
        <div class="row mb-3">
            <div class="col-md-6 col-lg-3 mb-3">
                <label for="order_number" class="form-label">Order #</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                    <input type="text" class="form-control" id="order_number" name="order_number" value="{{ request('order_number') }}" placeholder="Enter order number">
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <label for="invoice_number" class="form-label">Invoice #</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                    <input type="text" class="form-control" id="invoice_number" name="invoice_number" value="{{ request('invoice_number') }}" placeholder="Enter invoice number">
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" id="username" name="username" value="{{ request('username') }}" placeholder="Enter username">
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-3">
                <label for="company_name" class="form-label">Company/Franchise</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="{{ request('company_name') }}" placeholder="Enter company name">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Date Range</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                    <input type="text" class="form-control date-picker" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="From date">
                    <span class="input-group-text">to</span>
                    <input type="text" class="form-control date-picker" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="To date">
                </div>
            </div>
            
            <div class="col-md-6 d-flex align-items-end">
                <div class="filter-buttons ms-auto">
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@if(request()->anyFilled(['order_number', 'invoice_number', 'status', 'username', 'company_name', 'date_from', 'date_to']))
    <div class="active-filters d-flex flex-wrap align-items-center">
        <span class="fw-bold me-3 mb-2">Active Filters:</span>

        @if(request('order_number'))
            <span class="badge rounded-pill">
                <span class="badge-label">Order #:</span> {{ request('order_number') }}
            </span>
        @endif

        @if(request('invoice_number'))
            <span class="badge rounded-pill">
                <span class="badge-label">Invoice #:</span> {{ request('invoice_number') }}
            </span>
        @endif

        @if(request('status'))
            <span class="badge rounded-pill">
                <span class="badge-label">Status:</span>
                @php
                    $status = request('status');
                    if(is_array($status)) {
                        echo implode(', ', array_map('ucfirst', $status));
                    } else {
                        echo ucfirst($status);
                    }
                @endphp
            </span>
        @endif

        @if(request('username'))
            <span class="badge rounded-pill">
                <span class="badge-label">Username:</span> {{ request('username') }}
            </span>
        @endif

        @if(request('company_name'))
            <span class="badge rounded-pill">
                <span class="badge-label">Company:</span> {{ request('company_name') }}
            </span>
        @endif

        @if(request('date_from') || request('date_to'))
            <span class="badge rounded-pill">
                <span class="badge-label">Date Range:</span>
                {{ request('date_from') ?: 'Any' }} - {{ request('date_to') ?: 'Any' }}
            </span>
        @endif
        
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-danger ms-auto">
            <i class="fas fa-times me-1"></i> Clear All Filters
        </a>
    </div>
@endif

<div class="data-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Invoice #</th>
                    <th>Franchisee</th>
                    <th>Company/Franchise</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>QuickBooks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>#{{ $order->id }}</td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="text-muted"><i class="fas fa-clock"></i> Pending</span>
                            @elseif($order->invoice_number)
                                <span class="text-primary">{{ $order->invoice_number }}</span>
                            @else
                                <span class="text-muted">Not generated</span>
                            @endif
                        </td>
                        <td>{{ $order->user->username ?? 'Unknown' }}</td>
                        <td>{{ $order->user->franchiseeProfile->company_name ?? 'Unknown' }}</td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge rounded-pill bg-warning text-dark">Pending Approval</span>
                            @elseif($order->status == 'approved')
                                <span class="badge rounded-pill bg-primary">Awaiting Fulfillment</span>
                            @elseif($order->status == 'packed')
                                <span class="badge rounded-pill bg-info">In Progress</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge rounded-pill bg-success">Shipped</span>
                            @elseif($order->status == 'delivered')
                                <span class="badge rounded-pill bg-success">Delivered</span>
                            @elseif($order->status == 'rejected')
                                <span class="badge rounded-pill bg-danger">Rejected</span>
                            @else
                                <span class="badge rounded-pill bg-secondary">{{ ucfirst($order->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($order->qb_invoice_id)
                                <span class="text-success"><i class="fas fa-check-circle"></i> {{ $order->qb_invoice_id }}</span>
                            @else
                                <span class="text-muted">Not Synced</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <!-- Additional action buttons can go here -->
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">No orders found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-3 border-top">
        {{ $orders->appends(request()->query())->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date pickers
        const datePickers = document.querySelectorAll('.date-picker');
        datePickers.forEach(input => {
            flatpickr(input, {
                dateFormat: "Y-m-d",
                allowInput: true,
                altInput: true,
                altFormat: "F j, Y",
            });
        });
        
        // Handle form reset
        document.querySelector('#orders-filter-form button[type="reset"]').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Clear all form inputs
            const form = document.getElementById('orders-filter-form');
            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.value = '';
            });
            
            // Reset Flatpickr instances
            datePickers.forEach(input => {
                if (input._flatpickr) {
                    input._flatpickr.clear();
                }
            });
        });
    });
</script>
@endsection