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
                        <th>Product</th>
                        <th>Variant</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
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
                        <th colspan="4" class="text-end">Total:</th>
                        <th>${{ number_format($order->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection