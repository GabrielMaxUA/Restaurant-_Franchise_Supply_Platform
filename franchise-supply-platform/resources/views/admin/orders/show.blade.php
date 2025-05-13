@extends('layouts.admin')

@section('title', 'Order Details - Restaurant Franchise Supply Platform')

@section('page-title', 'Order #' . $order->id)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
</div>

<!-- Order Summary and Shipping Information Row -->
<div class="row mb-4">
    <!-- Order Summary -->
    <div class="col-md-6">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Summary</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Order ID:</strong> #{{ $order->id }}
                </div>
                <div class="mb-3">
                    <strong>Invoice #:</strong>
                    @if($order->status == 'pending')
                        <span class="text-muted">Pending approval</span>
                    @elseif($order->invoice_number)
                        <span class="text-primary">{{ $order->invoice_number }}</span>
                    @else
                        <span class="text-muted">Not generated</span>
                    @endif
                </div>
                <div class="mb-3">
                    <strong>Status:</strong>
                    @if($order->status == 'pending')
                        <span class="badge rounded-pill bg-warning text-dark order-status-badge">Pending Approval</span>
                    @elseif($order->status == 'approved')
                        <span class="badge rounded-pill bg-primary order-status-badge">Awaiting Fulfillment</span>
                    @elseif($order->status == 'packed')
                        <span class="badge rounded-pill bg-info order-status-badge">In Progress</span>
                    @elseif($order->status == 'shipped')
                        <span class="badge rounded-pill bg-success order-status-badge">Shipped</span>
                    @elseif($order->status == 'delivered')
                        <span class="badge rounded-pill bg-success order-status-badge">Delivered</span>
                    @elseif($order->status == 'rejected')
                        <span class="badge rounded-pill bg-danger order-status-badge">Rejected</span>
                    @else
                        <span class="badge rounded-pill bg-secondary order-status-badge">{{ ucfirst($order->status) }}</span>
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
    
    <!-- Shipping Information -->
    <div class="col-md-6">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Shipping Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Shipping Address:</strong> {{ $order->shipping_address }}
                </div>
                <div class="mb-3">
                    <strong>City:</strong> {{ $order->shipping_city }}
                </div>
                <div class="mb-3">
                    <strong>State:</strong> {{ $order->shipping_state }}
                </div>
                <div class="mb-3">
                    <strong>ZIP Code:</strong> {{ $order->shipping_zip }}
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <strong>Delivery Date:</strong> {{ $order->local_delivery_date ? $order->local_delivery_date->format('Y-m-d') : 'Not scheduled' }}
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
                        Express Delivery
                    @else
                        {{ $order->delivery_preference ?? 'Standard' }}
                    @endif
                </div>
                <div class="mb-3">
                    <strong>Contact Phone:</strong> {{ $order->contact_phone ?? ($order->user->phone ?? 'Not provided') }}
                </div>
                
                @if($order->notes)
                    <hr>
                    <div>
                        <strong>Order Notes:</strong>
                        <p class="mt-2 mb-0">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Order Items -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
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
    </div>
</div>

<!-- Order Actions -->
<div class="row mb-4">
    <div class="col-md-2"></div>
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Order Actions</h6>
            </div>
            <div class="card-body text-center">
                @if($order->status == 'pending')
                    <div class="d-flex justify-content-center gap-3 action-buttons">
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="approved">
                            <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this order?')">
                                <i class="fas fa-check me-2"></i>Approve Order
                            </button>
                        </form>

                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this order?')">
                                <i class="fas fa-times me-2"></i>Reject Order
                            </button>
                        </form>
                    </div>
                @elseif($order->status == 'approved')
                    <div class="d-flex justify-content-center gap-3 action-buttons">
                        <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="packed">
                            <button type="submit" class="btn btn-info text-white">
                                <i class="fas fa-box me-2"></i>Mark as Packed
                            </button>
                        </form>

                        @if(!$order->qb_invoice_id)
                            <form action="{{ route('admin.orders.sync-quickbooks', $order) }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sync me-2"></i>Sync to QuickBooks
                                </button>
                            </form>
                        @endif
                    </div>
                @elseif($order->status == 'packed')
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="shipped">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-shipping-fast me-2"></i>Mark as Shipped
                        </button>
                    </form>
                @elseif($order->status == 'shipped')
                    <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="m-0 d-inline-block">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle me-2"></i>Mark as Delivered
                        </button>
                    </form>
                @elseif($order->status == 'delivered' || $order->status == 'rejected')
                    <div class="alert alert-info d-inline-block mb-0">
                        <i class="fas fa-info-circle me-2"></i>No actions available for this order status.
                    </div>
                @endif

                @if(in_array($order->status, ['approved', 'packed', 'shipped', 'delivered']))
                    <div class="mt-3">
                        <a href="{{ route('franchisee.orders.invoice', ['id' => $order->id]) }}?print=true" target="_blank" class="btn btn-outline-primary">
                            <i class="fas fa-file-invoice me-2"></i> View & Print Invoice
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-2"></div>
</div>
@endsection