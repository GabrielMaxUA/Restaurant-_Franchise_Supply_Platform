@extends('layouts.admin')

@section('title', 'Dashboard - Restaurant Franchise Supply Platform')

@section('page-title', 'Dashboard')

@section('content')
<!-- Action Blocks -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0">
            <div class="card-body py-3">
                <div class="row g-2 text-center">
                    <div class="col-md-3">
                        <a href="{{ route('admin.orders.index') }}" class="action-block p-3 d-block rounded">
                            <div class="icon-wrapper rounded-circle bg-primary-subtle mb-2 mx-auto">
                                <i class="fas fa-shopping-cart text-primary"></i>
                            </div>
                            <h6 class="mb-0">Manage Orders</h6>
                            <small class="text-muted">Process and track orders</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.products.index') }}" class="action-block p-3 d-block rounded">
                            <div class="icon-wrapper rounded-circle bg-success-subtle mb-2 mx-auto">
                                <i class="fas fa-box text-success"></i>
                            </div>
                            <h6 class="mb-0">Product Catalog</h6>
                            <small class="text-muted">Manage your inventory</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.categories.index') }}" class="action-block p-3 d-block rounded">
                            <div class="icon-wrapper rounded-circle bg-info-subtle mb-2 mx-auto">
                                <i class="fas fa-tags text-info"></i>
                            </div>
                            <h6 class="mb-0">Categories</h6>
                            <small class="text-muted">Organize your products</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.users.index') }}" class="action-block p-3 d-block rounded">
                            <div class="icon-wrapper rounded-circle bg-warning-subtle mb-2 mx-auto">
                                <i class="fas fa-users text-warning"></i>
                            </div>
                            <h6 class="mb-0">User Management</h6>
                            <small class="text-muted">Manage franchisees</small>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="action-block p-3 d-block rounded">
                            <div class="icon-wrapper rounded-circle bg-success-subtle mb-2 mx-auto">
                                <i class="fas fa-dollar-sign text-success"></i>
                            </div>
                            <h6 class="mb-0">Total Paid</h6>
                            <small class="text-muted">${{ number_format($totalPaidAmount ?? 0, 2) }}</small>
                        </a>
                    </div>
                    <!-- <div class="col-md-3">
                        <a href="{{ route('admin.orders.index') }}?not_status[]=delivered&not_status[]=rejected" class="action-block p-3 d-block rounded">
                            <div class="icon-wrapper rounded-circle bg-warning-subtle mb-2 mx-auto">
                                <i class="fas fa-dollar-sign text-warning"></i>
                            </div>
                            <h6 class="mb-0">Pending Payments</h6>
                            <small class="text-muted">${{ number_format($unpaidAmount ?? 0, 2) }}</small>
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">View All Orders</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Franchisee</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentOrders) && count($recentOrders) > 0)
                                @foreach($recentOrders as $order)
                                <tr>
                                    <td class="fw-bold">#{{ $order->id }}</td>
                                    <td>{{ $order->user->username ?? 'Unknown' }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td>
                                        @if($order->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
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
                                        @else
                                            <span class="badge bg-danger">{{ is_string($order->status) ? ucfirst($order->status) : 'Unknown' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            @if($order->status == 'delivered' || $order->status == 'rejected')
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                            <p class="mb-0">No recent orders found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    /* Action blocks styling */
    .action-block {
        transition: all 0.2s ease;
        border: 1px solid #e3e6f0;
        text-decoration: none;
        color: inherit;
    }
    
    .action-block:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        color: inherit;
    }
    
    .icon-wrapper {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .icon-wrapper i {
        font-size: 1.5rem;
    }
    
    /* Cards with left borders */
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    
    /* Chart containers */
    .chart-area {
        position: relative;
        height: 24rem;
        width: 100%;
    }
    
    /* Background colors for action blocks */
    .bg-primary-subtle {
        background-color: rgba(78, 115, 223, 0.1);
    }
    
    .bg-success-subtle {
        background-color: rgba(28, 200, 138, 0.1);
    }
    
    .bg-info-subtle {
        background-color: rgba(54, 185, 204, 0.1);
    }
    
    .bg-warning-subtle {
        background-color: rgba(246, 194, 62, 0.1);
    }
    
    .bg-danger-subtle {
        background-color: rgba(231, 74, 59, 0.1);
    }
    
    /* Badge styling */
    .badge.bg-warning.text-dark {
        background-color: #f6c23e !important;
        border: 1px solid #f6c23e;
    }
    
    /* Table styling */
    .table thead th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    /* Chart view buttons */
    .btn-group .btn-outline-primary.active {
        background-color: #4e73df;
        color: #fff;
    }
    
    .col-md-3{
      width: calc(100% / 5);
    }

</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection