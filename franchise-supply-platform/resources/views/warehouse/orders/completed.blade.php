@extends('layouts.warehouse')

@section('title', 'Completed Orders - Warehouse')

@section('page-title', $pageTitle ?? 'Completed Orders')

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to All Orders
    </a>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-success">Delivered Orders</h6>
        <div>
            <a href="{{ route('warehouse.orders.fulfillment-report') }}" class="btn btn-sm btn-info">
                <i class="fas fa-chart-bar"></i> Fulfillment Report
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Customer</th>
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
                            <td>{{ $order->delivered_at ? Carbon\Carbon::parse($order->delivered_at)->format('M d, Y') : $order->updated_at->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $order->user->username }}</strong><br>
                                <small>{{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : 'N/A' }}</small>
                            </td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <a href="{{ route('warehouse.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No completed orders found</td>
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

<!-- Fulfillment Metrics -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Fulfillment Metrics</h6>
            </div>
            <div class="card-body">
                @php
                    // Calculate average fulfillment time in hours (this is simplified - real app would use proper timestamps)
                    $totalFulfillmentTime = 0;
                    $orderCount = count($orders);
                    
                    foreach ($orders as $order) {
                        $totalFulfillmentTime += $order->created_at->diffInHours($order->updated_at);
                    }
                    
                    $avgFulfillmentTime = $orderCount > 0 ? $totalFulfillmentTime / $orderCount : 0;
                    $avgFulfillmentDays = round($avgFulfillmentTime / 24, 1);
                @endphp
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-primary">{{ $orderCount }}</h3>
                                <p class="mb-0">Orders Fulfilled</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-info">{{ $avgFulfillmentDays }}</h3>
                                <p class="mb-0">Avg. Days to Fulfill</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Fulfillment Performance</h5>
                        <p class="card-text">Your warehouse team has successfully processed and delivered {{ $orderCount }} orders. This contributes to customer satisfaction and timely product delivery.</p>
                        <p class="card-text">For more detailed metrics and performance analysis, check the full <a href="{{ route('warehouse.orders.fulfillment-report') }}">Fulfillment Report</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Order Archive</h6>
            </div>
            <div class="card-body">
                <p>Completed orders are kept in the system for reference and reporting purposes. You can:</p>
                
                <ul class="mb-4">
                    <li>View order details including all items and shipping information</li>
                    <li>Access original packing slips and shipping labels</li>
                    <li>Analyze fulfillment patterns with the Fulfillment Report</li>
                </ul>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Orders remain in this list for 90 days after delivery. After that, they are archived but still accessible through the reporting system.
                </div>
                
                <div class="form-group mt-4">
                    <label for="search_archived">Search Archived Orders</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search_archived" placeholder="Order # or Customer Name">
                        <button class="btn btn-outline-secondary" type="button">Search</button>
                    </div>
                    <small class="form-text text-muted">Contact IT support for access to orders older than 90 days.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection