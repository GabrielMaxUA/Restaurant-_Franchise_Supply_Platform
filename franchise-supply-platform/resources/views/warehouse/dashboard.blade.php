@extends('layouts.warehouse')

@section('title', 'Warehouse Dashboard - Restaurant Franchise Supply Platform')

@section('page-title', 'Warehouse Dashboard')

@section('content')
<div class="row">
    <!-- Inventory Summary Cards -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.products.index') }}" class="text-decoration-none">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Products</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalProducts ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Low Stock Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.products.index', ['inventory' => 'low_stock']) }}" class="text-decoration-none">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ 
                                    (\App\Models\Product::where('inventory_count', '<=', 10)
                                        ->where('inventory_count', '>', 0)->count()) + 
                                    (\App\Models\ProductVariant::where('inventory_count', '<=', 10)
                                        ->where('inventory_count', '>', 0)->count())
                                }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <!-- Out of Stock Products Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.products.index', ['inventory' => 'out_of_stock']) }}" class="text-decoration-none">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ 
                                    (\App\Models\Product::where('inventory_count', '=', 0)->count()) + 
                                    (\App\Models\ProductVariant::where('inventory_count', '=', 0)->count())
                                }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    
    <!-- Orders Waiting Fulfillment Card -->
    <div class="col-xl-3 col-md-6 mb-4">
        <a href="{{ route('warehouse.orders.index') }}" class="text-decoration-none">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="badge bg-info me-1">{{ $orders }}</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Low Stock Products Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning">Low Stock Products</h6>
                <a href="{{ route('warehouse.inventory.low-stock') }}" class="btn btn-sm btn-warning">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $displayedItems = 0;
                                $maxDisplayItems = 5;
                            @endphp
                            
                            @if(isset($lowStockProducts) && count($lowStockProducts) > 0)
                                @foreach($lowStockProducts as $product)
                                    @if($product->inventory_count > 0 && $product->inventory_count <= 10 && $displayedItems < $maxDisplayItems)
                                        @php $displayedItems++; @endphp
                                        <tr>
                                            <td>#{{ $product->id }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>-</td>
                                            <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                            <td>
                                                <span class="text-warning fw-bold">{{ $product->inventory_count }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('warehouse.products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Update Stock
                                                </a>
                                            </td>
                                        </tr>
                                    @endif

                                    @if(isset($product->variants) && count($product->variants) > 0)
                                        @foreach($product->variants as $variant)
                                            @if($variant->inventory_count > 0 && $variant->inventory_count <= 10 && $displayedItems < $maxDisplayItems)
                                                @php $displayedItems++; @endphp
                                                <tr>
                                                    <td>#{{ $product->id }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $variant->name }}</td>
                                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                                    <td>
                                                        <span class="text-warning fw-bold">{{ $variant->inventory_count }}</span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('warehouse.products.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i> Update Stock
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif

                            @if($displayedItems == 0)
                                <tr>
                                    <td colspan="6" class="text-center">No low stock products found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Out of Stock Products Section -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-danger">Out of Stock Products</h6>
                <a href="{{ route('warehouse.inventory.out-of-stock') }}" class="btn btn-sm btn-danger">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>Category</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $displayedItems = 0;
                                $maxDisplayItems = 5;
                                $hasItems = false;
                            @endphp
                            
                            @if(isset($outOfStockProducts) && count($outOfStockProducts) > 0)
                                @foreach($outOfStockProducts as $product)
                                    <!-- Display main product if it's out of stock -->
                                    @if($product->inventory_count == 0 && $displayedItems < $maxDisplayItems)
                                        @php 
                                            $displayedItems++; 
                                            $hasItems = true;
                                        @endphp
                                        <tr>
                                            <td>#{{ $product->id }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>-</td>
                                            <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                            <td>{{ $product->updated_at ? $product->updated_at->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('warehouse.products.edit', $product->id) }}" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-plus-circle"></i> Add Stock
                                                </a>
                                            </td>
                                        </tr>
                                    @endif

                                    <!-- Display any out-of-stock variants, even if main product has stock -->
                                    @if(isset($product->variants) && count($product->variants) > 0)
                                        @foreach($product->variants as $variant)
                                            @if($variant->inventory_count == 0 && $displayedItems < $maxDisplayItems)
                                                @php 
                                                    $displayedItems++; 
                                                    $hasItems = true;
                                                @endphp
                                                <tr>
                                                    <td>#{{ $product->id }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td>{{ $variant->name }}</td>
                                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                                    <td>{{ $variant->updated_at ? $variant->updated_at->format('M d, Y') : 'N/A' }}</td>
                                                    <td>
                                                        <a href="{{ route('warehouse.products.edit', $product->id) }}" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-plus-circle"></i> Add Stock
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            @endif

                            @if(!$hasItems)
                                <tr>
                                    <td colspan="6" class="text-center">No out of stock products or variants found</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Popular Products Section -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-success">Popular Products</h6>
                <a href="{{ route('warehouse.inventory.popular') }}" class="btn btn-sm btn-success">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Product</th>
                                <th>Variant</th>
                                <th>Category</th>
                                <th>Orders Count</th>
                                <th>Current Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($popularProducts) && count($popularProducts) > 0)
                                @foreach($popularProducts->take(5) as $product)
                                <tr>
                                    <td>#{{ $product->id }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>-</td>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    <td>{{ $product->orders_count ?? '0' }}</td>
                                    <td>
                                        @if($product->inventory_count <= 0)
                                            <span class="text-danger fw-bold">{{ $product->inventory_count }}</span>
                                        @elseif($product->inventory_count <= 10)
                                            <span class="text-warning fw-bold">{{ $product->inventory_count }}</span>
                                        @else
                                            <span class="text-success">{{ $product->inventory_count }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('warehouse.products.edit', $product->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Update Stock
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">No popular products data available</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Alert -->
<div class="row mt-4">
    <div class="col-12">
        @if(isset($approvedOrders) && $approvedOrders > 0)
        <div class="alert alert-primary mb-4 border-left-primary">
            <div class="d-flex align-items-center">
                <i class="fas fa-clipboard-check me-2"></i>
                <div>
                    <strong>{{ $approvedOrders }} {{ Str::plural('order', $approvedOrders) }}</strong> awaiting fulfillment
                    <a href="{{ route('warehouse.orders.index', ['status' => 'approved']) }}" class="btn btn-sm btn-primary ms-3">
                        Process Orders
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Orders Waiting Fulfillment Section -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4 border-left-info">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-info">Orders Waiting Fulfillment</h6>
                <a href="{{ route('warehouse.orders.pending') }}" class="btn btn-sm btn-info">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th class="text-center">Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td>#{{ $order->id }}</td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>${{ number_format($order->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge {{ $order->status == 'approved' ? 'bg-info' : ($order->status == 'packed' ? 'bg-primary' : 'bg-secondary') }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('warehouse.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No orders waiting for fulfillment</td>
                                </tr>
                            @endforelse
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
    .border-left-primary {
        border-left: 4px solid #4e73df;
    }
    
    .border-left-success {
        border-left: 4px solid #1cc88a;
    }
    
    .border-left-warning {
        border-left: 4px solid #f6c23e;
    }
    
    .border-left-danger {
        border-left: 4px solid #e74a3b;
    }
    
    .border-left-info {
        border-left: 4px solid #36b9cc;
    }
    
    .border-left-secondary {
        border-left: 4px solid #858796;
    }
    
    /* Alert styling with left border */
    .alert.border-left-primary,
    .alert.border-left-info {
        border-left-width: 4px;
        border-left-style: solid;
        border-top-left-radius: 4px;
        border-bottom-left-radius: 4px;
    }
</style>
@endsection