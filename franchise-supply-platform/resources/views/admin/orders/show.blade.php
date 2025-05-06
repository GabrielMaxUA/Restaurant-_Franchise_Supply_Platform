@extends('layouts.admin')

@section('title', 'Order Details - Restaurant Franchise Supply Platform')

@section('page-title', 'Order #' . $order->id)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="row">
    <!-- Order Summary -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Order ID:</strong> #{{ $order->id }}
                </div>
                <div class="mb-3">
                    <strong>Status:</strong>
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
                </div>
                <div class="mb-3">
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y H:i') }}
                </div>
                <div class="mb-3">
                    <strong>Total Amount:</strong> ${{ number_format($order->total_amount, 2) }}
                </div>
                <div class="mb-3">
                    <strong>QuickBooks Invoice:</strong> 
                    @if($order->qb_invoice_id)
                        <span class="text-success">{{ $order->qb_invoice_id }}</span>
                    @else
                        <span class="text-muted">Not Synced</span>
                    @endif
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <strong>Franchisee:</strong> {{ $order->user->username ?? 'Unknown' }}
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> {{ $order->user->email ?? 'N/A' }}
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order Actions -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Actions</h6>
            </div>
            <div class="card-body">
                @if($order->status == 'pending')
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This order is awaiting your approval. Please review and take action.
                    </div>
                    
                    <div class="d-flex gap-2 mb-4">
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this order?')">
                                <i class="fas fa-check me-2"></i>Approve Order
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this order?')">
                                <i class="fas fa-times me-2"></i>Reject Order
                            </button>
                        </form>
                    </div>
                @elseif($order->status == 'approved')
                    <div class="alert alert-primary">
                        <i class="fas fa-info-circle me-2"></i>
                        This order has been approved and is ready for warehouse processing.
                    </div>
                    
                    @if(!$order->qb_invoice_id)
                        <div class="mb-4">
                            <form action="{{ route('admin.orders.sync-quickbooks', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync me-2"></i>Sync to QuickBooks
                                </button>
                            </form>
                        </div>
                    @endif
                    
                    <div class="d-flex gap-2 mb-4">
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="packed">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-box me-2"></i>Mark as Packed
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this order?')">
                                <i class="fas fa-ban me-2"></i>Cancel Order
                            </button>
                        </form>
                    </div>
                @elseif($order->status == 'packed')
                    <div class="alert alert-info">
                        <i class="fas fa-box me-2"></i>
                        This order has been packed and is ready for shipping.
                    </div>
                    
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="mb-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-shipping-fast me-2"></i>Mark as Shipped
                        </button>
                    </form>
                @elseif($order->status == 'shipped')
                    <div class="alert alert-success">
                        <i class="fas fa-shipping-fast me-2"></i>
                        This order has been shipped to the franchisee.
                    </div>
                    
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="mb-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="btn btn-secondary">
                            <i class="fas fa-check-circle me-2"></i>Mark as Delivered
                        </button>
                    </form>
                @elseif($order->status == 'delivered')
                    <div class="alert alert-secondary">
                        <i class="fas fa-check-circle me-2"></i>
                        This order has been delivered to the franchisee.
                    </div>
                @elseif($order->status == 'rejected' || $order->status == 'cancelled')
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        This order has been {{ $order->status }}.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Shipping Information -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Shipping Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Shipping Address:</strong> {{ $order->shipping_address }}</p>
                <p><strong>City:</strong> {{ $order->shipping_city }}</p>
                <p><strong>State:</strong> {{ $order->shipping_state }}</p>
                <p><strong>ZIP Code:</strong> {{ $order->shipping_zip }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Delivery Date:</strong> 
                    @if($order->delivery_date)
                        {{ \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') }}
                    @else
                        Not specified
                    @endif
                </p>
                <p><strong>Delivery Time:</strong> 
                    @if($order->delivery_time == 'morning')
                        Morning (8:00 AM - 12:00 PM)
                    @elseif($order->delivery_time == 'afternoon')
                        Afternoon (12:00 PM - 4:00 PM)
                    @elseif($order->delivery_time == 'evening')
                        Evening (4:00 PM - 8:00 PM)
                    @else
                        {{ $order->delivery_time ?? 'Not specified' }}
                    @endif
                </p>
                <p><strong>Delivery Method:</strong> 
                    @if($order->delivery_preference == 'standard')
                        Standard Delivery
                    @elseif($order->delivery_preference == 'express')
                        Express Delivery
                    @else
                        {{ $order->delivery_preference ?? 'Standard' }}
                    @endif
                </p>
                <p><strong>Contact Phone:</strong> {{ $order->contact_phone ?? 'Not provided' }}</p>
            </div>
        </div>
        
        @if($order->notes)
            <div class="mt-3">
                <strong>Order Notes:</strong>
                <p class="mt-2">{{ $order->notes }}</p>
            </div>
        @endif
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
                <thead class="table-light">
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
                <tfoot class="table-light">
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