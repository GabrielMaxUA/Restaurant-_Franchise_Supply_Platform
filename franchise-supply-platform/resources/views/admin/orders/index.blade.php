@extends('layouts.admin')

@section('title', isset($username) ? "Orders for {$username} - Restaurant Franchise Supply Platform" : 'Orders - Restaurant Franchise Supply Platform')

@section('page-title', $pageTitle ?? 'Order Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .welcome-banner {
        background-color: #e2ebd8;
        border-radius: 0.5rem;
        border-left: 5px solid #28a745;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .search-card {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
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
    
    .active-filters .badge {
        display: inline-flex;
        align-items: center;
        background-color: #e9f4fe;
        color: #0d6efd;
        font-weight: normal;
        padding: 0.5rem 0.75rem;
        border-radius: 0.5rem;
    }
    
    .active-filters .badge-label {
        font-weight: bold;
        margin-right: 0.5rem;
        color: #0d6efd;
    }
</style>
@endsection

@section('content')
<!-- Welcome Banner (similar to the one on the User Management page) -->
@if(request()->routeIs('admin.orders.index') && !request()->anyFilled(['order_number', 'status', 'username', 'company_name', 'date_from', 'date_to']))
<div class="welcome-banner">
    <h4 class="mb-2">
        <i class="fas fa-star me-2"></i> Welcome back, {{ Auth::user()->username ?? 'Admin' }}!
    </h4>
    <p class="mb-2">Platform Status: <strong>{{ App\Models\Order::where('status', 'pending')->count() }}</strong> pending orders.</p>
    <p class="mb-0">Use the filters below to find specific orders or browse the complete order history.</p>
</div>
@endif

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
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>Packed</option>
                    <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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

@if(request()->anyFilled(['order_number', 'status', 'username', 'company_name', 'date_from', 'date_to']))
    <div class="active-filters">
        <span class="fw-bold me-2">Active Filters:</span>
        
        @if(request('order_number'))
            <span class="badge">
                <span class="badge-label">Order #:</span> {{ request('order_number') }}
            </span>
        @endif
        
        @if(request('status'))
            <span class="badge">
                <span class="badge-label">Status:</span> {{ ucfirst(request('status')) }}
            </span>
        @endif
        
        @if(request('username'))
            <span class="badge">
                <span class="badge-label">Username:</span> {{ request('username') }}
            </span>
        @endif
        
        @if(request('company_name'))
            <span class="badge">
                <span class="badge-label">Company:</span> {{ request('company_name') }}
            </span>
        @endif
        
        @if(request('date_from') || request('date_to'))
            <span class="badge">
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
                        <td>{{ $order->user->username ?? 'Unknown' }}</td>
                        <td>{{ $order->user->franchiseeProfile->company_name ?? 'Unknown' }}</td>
                        <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td>${{ number_format($order->total_amount, 2) }}</td>
                        <td>
                            @if($order->status == 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                            @elseif($order->status == 'approved')
                                <span class="badge bg-primary">Approved</span>
                            @elseif($order->status == 'packed')
                                <span class="badge bg-info">Packed</span>
                            @elseif($order->status == 'shipped')
                                <span class="badge bg-success">Shipped</span>
                            @elseif($order->status == 'delivered')
                                <span class="badge bg-secondary">Delivered</span>
                            @elseif($order->status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @elseif($order->status == 'cancelled')
                                <span class="badge bg-danger">Cancelled</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($order->status) }}</span>
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