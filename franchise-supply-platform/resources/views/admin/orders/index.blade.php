@extends('layouts.admin')

@section('title', isset($username) ? "Orders for {$username} - Restaurant Franchise Supply Platform" : 'Orders - Restaurant Franchise Supply Platform')
@section('page-title', $pageTitle ?? 'Order Management')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Filter Section -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="filters-form">
            <div class="row align-items-end">
                <!-- Search by Order Number -->
                <div class="col-md-2 mb-3">
                    <label for="order_number" class="form-label">Order #</label>
                    <input type="text" class="form-control" id="order_number" name="order_number" 
                           placeholder="Order number..." 
                           value="{{ request('order_number') }}">
                </div>
                
                <!-- Search by Customer Name -->
                <div class="col-md-3 mb-3">
                    <label for="username" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Search customer..." 
                           value="{{ request('username') }}">
                </div>
                
                <!-- Search by Company Name -->
                <div class="col-md-3 mb-3">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" 
                           placeholder="Search company..." 
                           value="{{ request('company_name') }}">
                </div>
                
                <!-- Status Filter -->
                <div class="col-md-2 mb-3">
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
                
                <!-- Filter Actions -->
                <div class="col-md-2 mb-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="data-table">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Invoice #</th>
                    <th>Franchisee</th>
                    <th>Company</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th class="text-center">Status</th>
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
                    <td class="text-center">
                        @php
                            $badge = match($order->status) {
                                'pending' => 'bg-secondary',
                                'approved' => 'bg-info',
                                'processing' => 'bg-info',
                                'packed' => 'bg-warning',
                                'shipped' => 'bg-primary',
                                'delivered' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'cancelled' => 'bg-danger',
                                default => 'bg-secondary',
                            };
                        @endphp
                        <span class="badge rounded-pill {{ $badge }}">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        @if($order->qb_invoice_id)
                            <span class="text-success"><i class="fas fa-check-circle"></i> {{ $order->qb_invoice_id }}</span>
                        @else
                            <span class="text-muted">Not Synced</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('components.pagination', ['items' => $orders])
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        flatpickr('.date-picker', {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "F j, Y",
            allowInput: true
        });
    });
</script>
@endsection
