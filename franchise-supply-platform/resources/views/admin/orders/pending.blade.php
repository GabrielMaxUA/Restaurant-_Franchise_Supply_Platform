@extends('layouts.admin')

@section('title', 'Orders Awaiting Approval')

@section('page-title', $pageTitle)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to All Orders
    </a>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-warning">Orders Awaiting Approval</h6>
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
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary mb-1">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <div class="btn-group mt-1">
                                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to approve this order?')">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="d-inline ms-1">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to reject this order? This will return inventory to stock.')">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No orders awaiting approval</td>
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

<!-- Approval Guidelines -->
@if(count($orders) > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">Approval Guidelines</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-check-circle"></i> Approval Process</h5>
                        <p class="card-text">Review order details, customer information, and product availability before approving.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Rejection Criteria</h5>
                        <p class="card-text">Reject orders only for valid reasons like insufficient inventory, incorrect pricing, or suspicious activity.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-info-circle"></i> Important Note</h5>
                        <p class="card-text">Approving sends the order to the warehouse for fulfillment. Rejecting returns products to inventory.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection