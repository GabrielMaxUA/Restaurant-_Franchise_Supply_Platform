@extends('layouts.warehouse')

@section('title', 'Pending Orders - Warehouse')

@section('page-title', $pageTitle)

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to All Orders
    </a>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Orders Awaiting Fulfillment</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Delivery Request</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $order->user->username }}</strong><br>
                                <small>{{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : 'N/A' }}</small>
                            </td>
                            <td>
                                @if($order->delivery_date)
                                    {{ $order->delivery_date->format('M d, Y') }}<br>
                                    <small>
                                        @if($order->delivery_time == 'morning')
                                            Morning (8:00 AM - 12:00 PM)
                                        @elseif($order->delivery_time == 'afternoon')
                                            Afternoon (12:00 PM - 4:00 PM)
                                        @elseif($order->delivery_time == 'evening')
                                            Evening (4:00 PM - 8:00 PM)
                                        @else
                                            {{ $order->delivery_time ?? 'Not specified' }}
                                        @endif
                                    </small>
                                    @if($order->delivery_preference == 'express')
                                        <br><span class="badge badge-danger">EXPRESS</span>
                                    @endif
                                @else
                                    Not specified
                                @endif
                            </td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <a href="{{ route('warehouse.orders.show', $order->id) }}" class="btn btn-sm btn-primary mb-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" class="m-0">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="packed">
                                        <button type="submit" class="btn btn-sm btn-success w-100" onclick="return confirm('Mark this order as packed?')">
                                            <i class="fas fa-box"></i> Pack
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No orders waiting for fulfillment</td>
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

<!-- Tips for Warehouse Staff -->
@if(count($orders) > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">Fulfillment Tips</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clipboard-check"></i> Order Packing</h5>
                        <p class="card-text">Click "View" to see order details and print a packing slip to help collect all items.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-truck-loading"></i> Prioritize Orders</h5>
                        <p class="card-text">Process orders with EXPRESS delivery and earliest delivery dates first.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Missing Items</h5>
                        <p class="card-text">Contact management if inventory is insufficient to fulfill an order.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection