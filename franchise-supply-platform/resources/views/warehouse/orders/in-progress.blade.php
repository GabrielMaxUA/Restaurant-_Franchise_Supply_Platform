@extends('layouts.warehouse')

@section('title', 'Orders In Progress - Warehouse')

@section('page-title', $pageTitle ?? 'Orders In Progress')

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to All Orders
    </a>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-warning">Packed Orders Ready for Shipping</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Shipping Address</th>
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
                                {{ $order->shipping_address }}<br>
                                {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                            </td>
                            <td>{{ $order->items->sum('quantity') }}</td>
                            <td>${{ number_format($order->total_amount, 2) }}</td>
                            <td>
                                <div class="d-flex flex-column action-buttons">
                                    <a href="{{ route('warehouse.orders.show', $order->id) }}" class="btn btn-sm btn-primary mb-2">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    <div class="d-flex justify-content-between mb-2">
                                        <a href="{{ route('warehouse.orders.packing-slip', $order->id) }}" class="btn btn-sm btn-info me-1 flex-grow-1" target="_blank">
                                            <i class="fas fa-print"></i> Packing Slip
                                        </a>
                                        <a href="{{ route('warehouse.orders.shipping-label', $order->id) }}" class="btn btn-sm btn-secondary ms-1 flex-grow-1" target="_blank">
                                            <i class="fas fa-tag"></i> Shipping Label
                                        </a>
                                    </div>

                                    <!-- Ship Button with Modal Trigger -->
                                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#shipModal{{ $order->id }}">
                                        <i class="fas fa-shipping-fast"></i> Ship
                                    </button>
                                </div>
                                
                                <!-- Ship Modal -->
                                <div class="modal fade" id="shipModal{{ $order->id }}" tabindex="-1" aria-labelledby="shipModalLabel{{ $order->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="shipped">
                                                
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="shipModalLabel{{ $order->id }}">Ship Order #{{ $order->id }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="tracking_number" class="form-label">Tracking Number (optional)</label>
                                                        <input type="text" class="form-control" id="tracking_number" name="tracking_number">
                                                        <div class="form-text">Add a tracking number for customer reference</div>
                                                    </div>
                                                    
                                                    <div class="alert alert-info">
                                                        <strong>Note:</strong> After marking this order as shipped, it will be moved to the "Shipped" list.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Mark as Shipped</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No orders currently in progress</td>
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

<!-- Shipment Process Guide -->
@if(count($orders) > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-info">Shipping Guidelines</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-print"></i> Step 1</h5>
                        <p class="card-text">Print the shipping label for each order.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-box"></i> Step 2</h5>
                        <p class="card-text">Verify all items are packed securely with the packing slip inside.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tag"></i> Step 3</h5>
                        <p class="card-text">Apply the shipping label to the outside of the package.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-shipping-fast"></i> Step 4</h5>
                        <p class="card-text">Record tracking info and mark the order as shipped.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection