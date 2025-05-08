@extends('layouts.admin')

@section('title', isset($username) ? "Orders for {$username} - Restaurant Franchise Supply Platform" : 'Orders - Restaurant Franchise Supply Platform')

@section('page-title', $pageTitle ?? 'Order Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">{{ $pageTitle ?? 'Manage Orders' }}</h1>
    
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

<div class="card shadow">
    <div class="card-body">
        @if(isset($username))
            <div class="alert alert-info mb-4">
                <i class="fas fa-filter me-2"></i> Showing orders for: <strong>{{ $username }}</strong>
            </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Franchisee</th>
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
                            <td>{{ $order->created_at->format('M d, Y H:i') }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                @if($order->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
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
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $orders->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection