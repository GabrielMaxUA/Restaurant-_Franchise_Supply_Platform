@extends('layouts.warehouse')

@section('title', 'Shipped Orders - Warehouse')

@section('page-title', $pageTitle ?? 'Shipped Orders')

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to All Orders
    </a>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-info">Orders In Transit</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Shipped Date</th>
                        <th>Customer</th>
                        <th>Tracking</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->updated_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $order->user->username }}</strong><br>
                                <small>{{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : 'N/A' }}</small>
                            </td>
                            <td>
                                @if($order->tracking_number)
                                    <span class="text-primary">{{ $order->tracking_number }}</span>
                                @else
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <div class="d-flex flex-column action-buttons">
                                    <a href="{{ route('warehouse.orders.show', $order->id) }}" class="btn btn-sm btn-primary mb-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>

                                    <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="delivered">
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Mark this order as delivered?')">
                                            <i class="fas fa-check-circle"></i> Mark Delivered
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No shipped orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>

<!-- Delivery Confirmation Guide -->
@if(count($orders) > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">Delivery Tracking</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8">
                <p>Orders that have been shipped but not yet delivered will appear in this list. Once a shipment is confirmed as delivered:</p>
                <ol>
                    <li>Verify delivery with the tracking information (if available)</li>
                    <li>Click "Mark Delivered" to update the order status</li>
                    <li>The order will then move to the "Completed Orders" section</li>
                </ol>
                <p>If a customer reports any issues with their delivery, contact your supervisor before updating the order status.</p>
            </div>
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-truck-loading"></i> Average Delivery Time</h5>
                        <p class="card-text">Standard delivery typically takes 3-5 business days. Express delivery takes 1-2 business days.</p>
                        <small class="text-muted">For delivery questions, contact logistics@example.com</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection