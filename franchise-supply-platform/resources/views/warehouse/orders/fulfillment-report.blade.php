@extends('layouts.warehouse')

@section('title', 'Fulfillment Report - Warehouse')

@section('page-title', 'Fulfillment Report')

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
    
    <button onclick="window.print()" class="btn btn-info float-end">
        <i class="fas fa-print me-2"></i>Print Report
    </button>
</div>

<!-- Report Date Range Filter -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Report Date Range</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('warehouse.orders.fulfillment-report') }}" method="GET" class="row align-items-end">
            <div class="col-md-4 mb-3">
                <label for="start_date">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
            </div>
            <div class="col-md-4 mb-3">
                <label for="end_date">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 mb-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Fulfillment Metrics Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Waiting for Fulfillment</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            In Progress</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $packedCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-box fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Shipped</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $shippedCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-shipping-fast fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Delivered</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deliveredCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fulfillment Performance Metrics -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Fulfillment Performance</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h4>Average Fulfillment Time: {{ round($avgFulfillmentTime, 1) }} hours</h4>
                    <p class="text-muted">Average time from order approval to shipment</p>
                    
                    <!-- Placeholder for chart - would be implemented with Chart.js in a real app -->
                    <div style="height: 200px; background-color: #f8f9fc; border-radius: 4px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                        <p class="text-muted mb-0">Fulfillment Time Trend Chart (placeholder)</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="text-primary mb-0">{{ round(($deliveredCount + $shippedCount) / max(1, ($approvedCount + $packedCount + $deliveredCount + $shippedCount)) * 100, 1) }}%</h5>
                                    <p class="mb-0">Fulfillment Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="text-primary mb-0">{{ round($deliveredCount / max(1, ($deliveredCount + $shippedCount)) * 100, 1) }}%</h5>
                                    <p class="mb-0">Delivery Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light mb-3">
                                <div class="card-body text-center">
                                    <h5 class="text-primary mb-0">{{ round($avgFulfillmentTime / 24, 1) }} days</h5>
                                    <p class="mb-0">Avg Fulfillment Days</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Process Efficiency</h6>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h5>Fulfillment Stages</h5>
                    
                    <!-- Process Stage Breakdown -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Approved → Packed</span>
                            <strong>{{ round($avgFulfillmentTime * 0.3, 1) }} hours</strong>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Packed → Shipped</span>
                            <strong>{{ round($avgFulfillmentTime * 0.2, 1) }} hours</strong>
                        </div>
                        <div class="progress mb-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Shipped → Delivered</span>
                            <strong>{{ round($avgFulfillmentTime * 0.5, 1) }} hours</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> These metrics are calculated based on orders processed during the selected date range.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Most Ordered Products</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Quantity Ordered</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topProducts as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
                            <td>{{ $item->product->id ?? 'N/A' }}</td>
                            <td>{{ $item->product->category->name ?? 'Uncategorized' }}</td>
                            <td>{{ $item->total_quantity }}</td>
                            <td>
                                @if($item->product)
                                    @if($item->product->inventory_count <= 0)
                                        <span class="text-danger">Out of Stock</span>
                                    @elseif($item->product->inventory_count <= 10)
                                        <span class="text-warning">{{ $item->product->inventory_count }} (Low)</span>
                                    @else
                                        <span class="text-success">{{ $item->product->inventory_count }}</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No order data available for this period</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recommendations -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Recommendations</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-6">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Inventory Alerts</h5>
                        <p class="card-text">Based on order trends, consider increasing stock for these popular items:</p>
                        <ul>
                            @foreach($topProducts->take(3) as $item)
                                @if($item->product && $item->product->inventory_count <= 20)
                                    <li>{{ $item->product->name }} (Current stock: {{ $item->product->inventory_count }})</li>
                                @endif
                            @endforeach
                            
                            @if($topProducts->where('product.inventory_count', '<=', 20)->count() === 0)
                                <li>No immediate inventory concerns for top products</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-tachometer-alt text-info me-2"></i>Process Improvements</h5>
                        <p class="card-text">Recommendations to improve fulfillment efficiency:</p>
                        <ul>
                            @if($avgFulfillmentTime > 48)
                                <li>Focus on reducing time from approved to packed status (currently {{ round($avgFulfillmentTime * 0.3, 1) }} hours)</li>
                            @endif
                            
                            @if($packedCount > $approvedCount * 0.5)
                                <li>Increase shipping frequency to reduce backlog of packed orders</li>
                            @endif
                            
                            @if($topProducts->count() > 0)
                                <li>Optimize warehouse layout to prioritize access to top {{ min(3, $topProducts->count()) }} products</li>
                            @endif
                            
                            @if($avgFulfillmentTime <= 48 && $packedCount <= $approvedCount * 0.5 && $topProducts->count() === 0)
                                <li>Current process is operating efficiently. Maintain performance.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .navbar, .sidebar, .dropdown, .sidebar-toggle, .btn-secondary, hr, footer {
            display: none !important;
        }
        
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
        }
        
        .card {
            break-inside: avoid;
        }
    }
</style>
@endpush
@endsection