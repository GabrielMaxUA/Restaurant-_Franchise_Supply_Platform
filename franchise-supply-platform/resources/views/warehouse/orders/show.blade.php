@extends('layouts.warehouse')

@section('title', 'Order #' . $order->id . ' - Warehouse')

@section('page-title', 'Order #' . $order->id)

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
    
    <div class="float-end">
        <a href="{{ route('warehouse.orders.packing-slip', $order->id) }}" class="btn btn-info" target="_blank">
            <i class="fas fa-print me-2"></i>Print Packing Slip
        </a>
        @if($order->status == 'packed' || $order->status == 'shipped' || $order->status == 'delivered')
            <a href="{{ route('warehouse.orders.shipping-label', $order->id) }}" class="btn btn-warning" target="_blank">
                <i class="fas fa-tag me-2"></i>Print Shipping Label
            </a>
        @endif
    </div>
</div>

<!-- Order Status Banner -->
<div class="card mb-4
    @if($order->status == 'approved') bg-primary text-white
    @elseif($order->status == 'packed') bg-warning
    @elseif($order->status == 'shipped') bg-info text-white
    @elseif($order->status == 'delivered') bg-success text-white
    @elseif($order->status == 'rejected') bg-danger text-white
    @elseif($order->status == 'pending') bg-secondary text-white
    @endif">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="card-title">
                    @if($order->status == 'approved')
                        <i class="fas fa-clipboard-list me-2"></i>Awaiting Fulfillment
                    @elseif($order->status == 'packed')
                        <i class="fas fa-box me-2"></i>In Progress (Packed)
                    @elseif($order->status == 'shipped')
                        <i class="fas fa-shipping-fast me-2"></i>Shipped
                    @elseif($order->status == 'delivered')
                        <i class="fas fa-check-circle me-2"></i>Delivered
                    @elseif($order->status == 'rejected')
                        <i class="fas fa-times-circle me-2"></i>Rejected by Admin
                    @elseif($order->status == 'pending')
                        <i class="fas fa-clock me-2"></i>Pending Admin Approval
                    @endif
                </h5>
                <p class="card-text mb-0">Order placed on {{ $order->created_at->format('F d, Y') }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                @if($order->status == 'approved')
                    <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="packed">
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-box me-2"></i>Mark as Packed
                        </button>
                    </form>
                @elseif($order->status == 'packed')
                    <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="shipped">

                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#shippingModal">
                            <i class="fas fa-shipping-fast me-2"></i>Mark as Shipped
                        </button>

                        <!-- Shipping Modal -->
                        <div class="modal fade" id="shippingModal" tabindex="-1" aria-labelledby="shippingModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title text-dark" id="shippingModalLabel">Shipping Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-start text-dark">
                                        <div class="mb-3">
                                            <label for="tracking_number" class="form-label">Tracking Number (optional)</label>
                                            <input type="text" class="form-control" id="tracking_number" name="tracking_number">
                                            <div class="form-text">Add a tracking number for customer reference</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Mark as Shipped</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                @elseif($order->status == 'shipped')
                    <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-check-circle me-2"></i>Mark as Delivered
                        </button>
                    </form>
                @elseif($order->status == 'rejected')
                    <span class="btn btn-light disabled">
                        <i class="fas fa-ban me-2"></i>No Actions Available
                    </span>
                @elseif($order->status == 'pending')
                    <span class="btn btn-light disabled">
                        <i class="fas fa-hourglass-half me-2"></i>Awaiting Admin Approval
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>

@if($order->status == 'rejected')
<!-- Rejection Notice for Warehouse Staff -->
<div class="alert alert-danger mb-4">
    <h5><i class="fas fa-exclamation-triangle me-2"></i>This Order Has Been Rejected</h5>
    <p>This order was rejected by an administrator and cannot be processed. The inventory has been automatically returned to stock.</p>
    <p><strong>Note:</strong> Only administrators can view or update rejected orders. If you believe this order was rejected in error, please contact an administrator.</p>
</div>
@endif

@if($order->status == 'pending')
<!-- Pending Notice for Warehouse Staff -->
<div class="alert alert-secondary mb-4">
    <h5><i class="fas fa-clock me-2"></i>This Order Is Pending Approval</h5>
    <p>This order is waiting for administrator approval before it can be processed by the warehouse.</p>
    <p><strong>Note:</strong> You can view the order details, but no actions can be taken until an administrator approves the order.</p>
</div>
@endif

<div class="row mb-4">
    <!-- Order Summary -->
    <div class="col-lg-6">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Order ID:</strong> #{{ $order->id }}
                </div>
                <div class="mb-3">
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}
                </div>
                <div class="mb-3">
                    <strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}
                </div>
                @if(($order->status == 'shipped' || $order->status == 'delivered') && $order->tracking_number)
                    <div class="mb-3">
                        <strong>Tracking Number:</strong> {{ $order->tracking_number }}
                    </div>
                @endif

                @if($order->shipped_at)
                    <div class="mb-3">
                        <strong>Shipped Date:</strong> {{ \Carbon\Carbon::parse($order->shipped_at)->format('M d, Y H:i') }}
                    </div>
                @endif

                @if($order->delivered_at)
                    <div class="mb-3">
                        <strong>Delivery Date:</strong> {{ \Carbon\Carbon::parse($order->delivered_at)->format('M d, Y H:i') }}
                    </div>
                @endif
                <hr>
                <div class="mb-3">
                    <strong>Customer:</strong> {{ $order->user->username }}
                </div>
                <div class="mb-3">
                    <strong>Company:</strong> {{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : 'N/A' }}
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> {{ $order->user->email }}
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong> {{ $order->contact_phone ?? ($order->user->phone ?? 'N/A') }}
                </div>
                @if($order->purchase_order)
                    <div class="mb-3">
                        <strong>Purchase Order #:</strong> {{ $order->purchase_order }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Shipping Information -->
    <div class="col-lg-6">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Shipping Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Shipping Address:</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Requested Delivery Date:</strong> 
                    {{ $order->delivery_date ? $order->delivery_date->format('M d, Y') : 'Not specified' }}
                </div>
                <div class="mb-3">
                    <strong>Delivery Time:</strong> 
                    @if($order->delivery_time == 'morning')
                        Morning (8:00 AM - 12:00 PM)
                    @elseif($order->delivery_time == 'afternoon')
                        Afternoon (12:00 PM - 4:00 PM)
                    @elseif($order->delivery_time == 'evening')
                        Evening (4:00 PM - 8:00 PM)
                    @else
                        {{ $order->delivery_time ?? 'Not specified' }}
                    @endif
                </div>
                <div class="mb-3">
                    <strong>Delivery Method:</strong> 
                    @if($order->delivery_preference == 'standard')
                        Standard Delivery
                    @elseif($order->delivery_preference == 'express')
                        <span class="text-danger">Express Delivery</span>
                    @else
                        {{ $order->delivery_preference ?? 'Standard' }}
                    @endif
                </div>
                
                @if($order->notes)
                    <hr>
                    <div class="mb-3">
                        <strong>Order Notes:</strong>
                        <div class="alert alert-info mt-2">{{ $order->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Order Items</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="50%">Product</th>
                        <th>Variant</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($item->product && $item->product->images && $item->product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $item->product->images->first()->image_url) }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="me-3" 
                                             style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center me-3" 
                                             style="width: 50px; height: 50px; border-radius: 4px;">
                                            <i class="fas fa-image text-secondary"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-weight-bold">{{ $item->product->name ?? 'Unknown Product' }}</div>
                                        <div class="small text-muted">ID: {{ $item->product->id ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $item->variant->name ?? 'N/A' }}</td>
                            <td>${{ number_format($item->price, 2) }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No items found</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Subtotal:</th>
                        <th>${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</th>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end">Shipping:</td>
                        <td>${{ number_format($order->shipping_cost ?? 0, 2) }}</td>
                    </tr>
                    <tr>
                        <th colspan="4" class="text-end">Total:</th>
                        <th>${{ number_format($order->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection