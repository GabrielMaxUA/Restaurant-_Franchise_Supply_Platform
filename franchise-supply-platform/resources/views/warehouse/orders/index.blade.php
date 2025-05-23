@extends('layouts.warehouse')

@section('title', 'Order Management - Warehouse')

@section('page-title', $pageTitle ?? 'Order Management')
<style>
    .pagination-wrapper {
        padding: 1rem;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
    .pagination-info {
        font-size: 0.875rem;
        color: #6c757d;
    }
    .pagination {
        display: inline-flex;
        list-style: none;
        border-radius: 0.375rem;
        padding-left: 0;
        margin: 0;
    }
    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 0.75rem;
        margin-left: -1px;
        line-height: 1.25;
        color: #4e73df;
        background-color: #fff;
        border: 1px solid #dee2e6;
        font-size: 0.875rem;
        min-width: 40px;
        text-align: center;
    }
    .page-item.active .page-link {
        background-color: #4e73df;
        color: #fff;
        border-color: #4e73df;
    }
    .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>

@section('content')
<div class="row mb-4">
    <!-- Order Status Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.orders.pending') }}" class="text-decoration-none">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Awaiting Fulfillment</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.orders.in-progress') }}" class="text-decoration-none">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $packedCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.orders.shipped') }}" class="text-decoration-none">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Shipped</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $shippedCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.orders.completed') }}" class="text-decoration-none">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deliveredCount ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Order Search and Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Search Orders</h6>
        <a href="{{ route('warehouse.orders.index') }}" class="btn btn-sm btn-secondary">Clear Filters</a>
    </div>
    <div class="card-body">
        <form action="{{ route('warehouse.orders.index') }}" method="GET">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="order_number">Order Number</label>
                    <input type="text" class="form-control" id="order_number" name="order_number" value="{{ request('order_number') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Awaiting Fulfillment</option>
                        <option value="packed" {{ request('status') == 'packed' ? 'selected' : '' }}>In Progress</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_from">Date From</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_to">Date To</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="username">Customer Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="{{ request('username') }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="company_name">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" value="{{ request('company_name') }}">
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Orders</h6>
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
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th class="text-center">Status</th>
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
                            <td class="text-center">
                                @if($order->status == 'approved')
                                    <span class="badge bg-primary rounded-pill order-status-badge">Awaiting Fulfillment</span>
                                @elseif($order->status == 'packed')
                                    <span class="badge bg-info rounded-pill order-status-badge">In Progress</span>
                                @elseif($order->status == 'shipped')
                                    <span class="badge bg-info rounded-pill order-status-badge">Shipped</span>
                                @elseif($order->status == 'delivered')
                                    <span class="badge bg-success rounded-pill order-status-badge">Delivered</span>
                                @elseif($order->status == 'rejected')
                                    <span class="badge bg-danger rounded-pill order-status-badge">Rejected</span>
                                @elseif($order->status == 'pending')
                                    <span class="badge bg-warning text-dark rounded-pill order-status-badge">Pending Approval</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill order-status-badge">{{ ucfirst($order->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center action-buttons">
                                    <a href="{{ route('warehouse.orders.show', $order->id) }}" class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-eye"></i> Details
                                    </a>

                                    @if($order->status == 'approved')
                                        <form action="{{ route('warehouse.orders.update-status', $order) }}" method="POST" class="d-inline m-0">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="packed">
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-box"></i> Pack
                                            </button>
                                        </form>
                                    @elseif($order->status == 'rejected' || $order->status == 'pending')
                                        <span class="badge bg-{{ $order->status == 'rejected' ? 'danger' : 'secondary' }} rounded-pill">
                                            <i class="fas fa-ban"></i> No Actions Available
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No orders found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
@if ($orders->hasPages())
    <div class="pagination-wrapper mt-4 text-center">
        <div class="pagination-info mb-2 text-muted small">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} results
        </div>
        <nav>
            <ul class="pagination justify-content-center mb-0">
                {{-- Previous Page Link --}}
                @if ($orders->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="fas fa-angle-left"></i>
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $orders->previousPageUrl() }}" rel="prev">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </li>
                @endif

                {{-- Page Numbers --}}
                @foreach ($orders->links()->elements[0] as $page => $url)
                    @if ($page == $orders->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($orders->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $orders->nextPageUrl() }}" rel="next">
                            <i class="fas fa-angle-right"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">
                            <i class="fas fa-angle-right"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
@endif

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Check for new orders every 60 seconds
    setInterval(function() {
        fetch('{{ route("warehouse.orders.check-new") }}')
            .then(response => response.json())
            .then(data => {
                // Update the pending orders count
                const approvedCount = data.approved_orders_count;
                
                // Flash the pending orders card if there are new orders
                if (approvedCount > {{ $approvedCount ?? 0 }}) {
                    const card = document.querySelector('.border-left-primary');
                    if (card) {
                        // Add flash effect
                        card.classList.add('bg-light');
                        setTimeout(() => {
                            card.classList.remove('bg-light');
                        }, 1000);
                        
                        // Update the count
                        const countElement = card.querySelector('.h5');
                        if (countElement) {
                            countElement.textContent = approvedCount;
                        }
                    }
                }
            })
            .catch(error => console.error('Error checking for new orders:', error));
    }, 60000);
</script>
@endsection