@extends('layouts.admin')

@section('title', 'Rejected Orders')

@section('page-title', 'Rejected Orders')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to All Orders
    </a>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-danger">Rejected Orders</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
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
                            <td>
                                <strong>{{ $order->user->username }}</strong><br>
                                <small>{{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : 'N/A' }}</small>
                            </td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No rejected orders found</td>
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

<!-- Rejection Information -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-danger">Rejection Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-8">
                <p><strong>About Rejected Orders:</strong></p>
                <ul>
                    <li>When an order is rejected, the inventory is automatically returned to stock.</li>
                    <li>The customer is notified via email about the rejection (if notification system is enabled).</li>
                    <li>Customers can view rejected orders in their order history with a "Rejected" status.</li>
                    <li>Reasons for rejection might include:
                        <ul>
                            <li>Insufficient inventory availability</li>
                            <li>Pricing discrepancies</li>
                            <li>Payment issues</li>
                            <li>Suspicious ordering activity</li>
                            <li>Other business reasons</li>
                        </ul>
                    </li>
                </ul>
                <p><strong>Note:</strong> It's good practice to include a reason when rejecting an order to help customers understand why their order wasn't fulfilled.</p>
            </div>
            <div class="col-lg-4">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chart-pie"></i> Rejection Statistics</h5>
                        <p class="card-text">Total rejected orders: <strong>{{ \App\Models\Order::where('status', 'rejected')->count() }}</strong></p>
                        <p class="card-text">Rejection rate: <strong>{{ 
                            number_format(
                                \App\Models\Order::where('status', 'rejected')->count() / 
                                max(1, \App\Models\Order::count()) * 100, 
                            1) }}%</strong>
                        </p>
                        <p class="card-text">Most common reason: <em>Insufficient inventory</em></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection