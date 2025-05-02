@extends('layouts.admin')

@section('title', 'Dashboard - Restaurant Franchise Supply Platform')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Orders Summary Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Pending Orders</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingOrders ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Summary Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Total Products</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Summary Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Franchisee Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $franchiseeUsers ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Summary Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Monthly Revenue</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">${{ $monthlyRevenue ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                <a href="{{ url('/admin/orders') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
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
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->user->username ?? 'Unknown' }}</td>
                                    <td>${{ $order->total_amount }}</td>
                                    <td>
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
                                        @else
                                            <span class="badge bg-danger">{{ ucfirst($order->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ url('/admin/orders/'.$order->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center">No recent orders found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Products -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-danger">Low Stock Products</h6>
                <a href="{{ url('/admin/products') }}" class="btn btn-sm btn-danger">View All Products</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($lowStockProducts) && count($lowStockProducts) > 0)
                                @foreach($lowStockProducts as $product)
                                <tr>
                                    <td>#{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    <td>
                                        <span class="text-danger fw-bold">{{ $product->inventory_count }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ url('/admin/products/'.$product->id.'/edit') }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Update Stock
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No low stock products found</td>
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