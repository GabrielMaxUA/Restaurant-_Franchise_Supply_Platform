@extends('layouts.franchisee')

@section('title', 'Franchisee Dashboard - Restaurant Supply Platform')

@section('page-title', 'Dashboard')

@section('styles')
<style>
    .stat-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
    }
    
    .stat-card .card-body {
        padding: 1.5rem;
    }
    
    .stat-card-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin-bottom: 1rem;
    }
    
    .inventory-alert {
        padding: 0.5rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .inventory-alert:hover {
        transform: translateX(5px);
    }
    
    .inventory-alert-critical {
        background-color: rgba(220, 53, 69, 0.1);
        border-left: 4px solid #dc3545;
    }
    
    .inventory-alert-warning {
        background-color: rgba(255, 193, 7, 0.1);
        border-left: 4px solid #ffc107;
    }
    
    .quick-action {
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    
    .quick-action:hover {
        transform: scale(1.05);
    }
    
    .chart-container {
        height: 300px;
        position: relative;
    }
    
    .activity-timeline .timeline-item {
        position: relative;
        padding-left: 40px;
        margin-bottom: 1.5rem;
    }
    
    .activity-timeline .timeline-item:before {
        content: "";
        position: absolute;
        left: 15px;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }
    
    .activity-timeline .timeline-item:last-child:before {
        height: 20px;
    }
    
    .activity-timeline .timeline-icon {
        position: absolute;
        left: 6px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background-color: #28a745;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    
    .promo-banner {
        background: linear-gradient(45deg, #28a745, #20c997);
        color: white;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
    }
    
    .promo-banner::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: url("{{ asset('images/pattern.svg') }}") repeat;
        opacity: 0.1;
        z-index: 0;
    }
    
    .promo-banner .content {
        position: relative;
        z-index: 1;
    }
</style>
@endsection

@section('content')

<!-- Key Metrics -->
<div class="row mb-4">
    <div class="col-md-3 mb-4 mb-md-0">
        <div class="card stat-card bg-light">
            <div class="card-body">
                <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                </div>
                <h5>Pending Orders</h5>
                <div class="d-flex align-items-baseline">
                    <h2 class="mb-0 me-2">{{ $stats['pending_orders'] ?? 0 }}</h2>
                    @if(isset($stats['pending_orders_change']))
                        @if($stats['pending_orders_change'] > 0)
                            <span class="text-success">
                                <i class="fas fa-arrow-up"></i> {{ $stats['pending_orders_change'] }}%
                            </span>
                        @elseif($stats['pending_orders_change'] < 0)
                            <span class="text-danger">
                                <i class="fas fa-arrow-down"></i> {{ abs($stats['pending_orders_change']) }}%
                            </span>
                        @else
                            <span class="text-muted">
                                <i class="fas fa-minus"></i> 0%
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-muted mb-0">Since last month</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4 mb-md-0">
        <div class="card stat-card bg-light">
            <div class="card-body">
                <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                    <i class="fas fa-dollar-sign fa-lg"></i>
                </div>
                <h5>Monthly Spending</h5>
                <div class="d-flex align-items-baseline">
                    <h2 class="mb-0 me-2">${{ number_format($stats['monthly_spending'] ?? 0, 2) }}</h2>
                    @if(isset($stats['spending_change']))
                        @if($stats['spending_change'] > 0)
                            <span class="text-danger">
                                <i class="fas fa-arrow-up"></i> {{ $stats['spending_change'] }}%
                            </span>
                        @elseif($stats['spending_change'] < 0)
                            <span class="text-success">
                                <i class="fas fa-arrow-down"></i> {{ abs($stats['spending_change']) }}%
                            </span>
                        @else
                            <span class="text-muted">
                                <i class="fas fa-minus"></i> 0%
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-muted mb-0">Since last month</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4 mb-md-0">
        <div class="card stat-card bg-light">
            <div class="card-body">
                <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                </div>
                <h5>Low Stock Items</h5>
                <div class="d-flex align-items-baseline">
                    <h2 class="mb-0 me-2">{{ $stats['low_stock_items'] ?? 0 }}</h2>
                    @if(isset($stats['low_stock_change']))
                        @if($stats['low_stock_change'] > 0)
                            <span class="text-danger">
                                <i class="fas fa-arrow-up"></i> {{ $stats['low_stock_change'] }}%
                            </span>
                        @elseif($stats['low_stock_change'] < 0)
                            <span class="text-success">
                                <i class="fas fa-arrow-down"></i> {{ abs($stats['low_stock_change']) }}%
                            </span>
                        @else
                            <span class="text-muted">
                                <i class="fas fa-minus"></i> 0%
                            </span>
                        @endif
                    @endif
                </div>
                <p class="text-muted mb-0">Items needing reorder</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-light">
            <div class="card-body">
                <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                    <i class="fas fa-truck-loading fa-lg"></i>
                </div>
                <h5>Incoming Deliveries</h5>
                <div class="d-flex align-items-baseline">
                    <h2 class="mb-0 me-2">{{ $stats['incoming_deliveries'] ?? 0 }}</h2>
                </div>
                <p class="text-muted mb-0">Expected this week</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Order Activity Chart -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Order Activity</h5>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary active" id="weekly-chart">Weekly</button>
                    <button type="button" class="btn btn-outline-secondary" id="monthly-chart">Monthly</button>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="orderActivityChart"></canvas>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Orders</h5>
                <a href="{{ route('franchisee.orders.pending') }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_orders as $order)
                            <tr>
                                <td>{{ $order->order_number }}</td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>{{ $order->items_count }} items</td>
                                <td>${{ number_format($order->total, 2) }}</td>
                                <td>
                                    @if($order->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($order->status == 'processing')
                                        <span class="badge bg-info">Processing</span>
                                    @elseif($order->status == 'shipped')
                                        <span class="badge bg-primary">Shipped</span>
                                    @elseif($order->status == 'out_for_delivery')
                                        <span class="badge bg-success">Out for Delivery</span>
                                    @elseif($order->status == 'delivered')
                                        <span class="badge bg-success">Delivered</span>
                                    @elseif($order->status == 'cancelled')
                                        <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('franchisee.orders.details', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-3">No recent orders found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Popular Products -->
        <div class="card mb-4 mb-lg-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Popular Products</h5>
                <a href="{{ route('franchisee.catalog', ['sort' => 'popular']) }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($popular_products as $product)
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <div style=" width: 60px; height: 60px;" class="me-3 d-flex align-items-center">
                                @if($product->images && $product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                         class="img-fluid rounded" alt="{{ $product->name }}">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center h-100 w-100 rounded">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $product->name }}</h6>
                                <p class="mb-0 small">
                                    <span class="text-success">${{ number_format($product->price, 2) }}</span>
                                    <span class="text-muted ms-2">{{ $product->unit_size }} {{ $product->unit_type }}</span>
                                </p>
                            </div>
                            <div>
                                <form action="{{ route('franchisee.cart.add') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('franchisee.catalog') }}" class="btn btn-success w-100 p-3 quick-action">
                            <i class="fas fa-shopping-cart fa-lg mb-2"></i>
                            <br>Place Order
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ route('franchisee.orders.pending') }}" class="btn btn-outline-info w-100 p-3 quick-action">
                            <i class="fas fa-shipping-fast fa-lg mb-2"></i>
                            <br>Track Orders
                        </a>
                    </div>
                    <div class="col-6 mt-2">
                        <a href="{{ route('franchisee.orders.history') }}" class="btn btn-outline-secondary w-100 p-3 quick-action">
                            <i class="fas fa-history fa-lg mb-2"></i>
                            <br>Order History
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <div class="activity-timeline">
                    @forelse($recent_activities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-icon">
                            @if($activity->type == 'order')
                                <i class="fas fa-shopping-cart"></i>
                            @elseif($activity->type == 'delivery')
                                <i class="fas fa-truck"></i>
                            @elseif($activity->type == 'payment')
                                <i class="fas fa-credit-card"></i>
                            @elseif($activity->type == 'inventory')
                                <i class="fas fa-box"></i>
                            @else
                                <i class="fas fa-bell"></i>
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $activity->title }}</h6>
                            <p class="mb-0 small text-muted">{{ $activity->description }}</p>
                            <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-3">
                        <p class="mb-0">No recent activity to display.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Order Activity Chart
        const ctx = document.getElementById('orderActivityChart').getContext('2d');
        const orderData = {
            weekly: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Orders',
                        data: {{ json_encode($charts['weekly_orders'] ?? [0, 0, 0, 0, 0, 0, 0]) }},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Spending ($)',
                        data: {{ json_encode($charts['weekly_spending'] ?? [0, 0, 0, 0, 0, 0, 0]) }},
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            monthly: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [
                    {
                        label: 'Orders',
                        data: {{ json_encode($charts['monthly_orders'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Spending ($)',
                        data: {{ json_encode($charts['monthly_spending'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }},
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            }
        };
        
        let currentChart = 'weekly';
        
        const orderChart = new Chart(ctx, {
            type: 'line',
            data: orderData[currentChart],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
        
        // Chart toggle buttons
        document.getElementById('weekly-chart').addEventListener('click', function() {
            if (currentChart !== 'weekly') {
                currentChart = 'weekly';
                orderChart.data = orderData[currentChart];
                orderChart.update();
                
                // Update active button
                document.getElementById('monthly-chart').classList.remove('active');
                this.classList.add('active');
            }
        });
        
        document.getElementById('monthly-chart').addEventListener('click', function() {
            if (currentChart !== 'monthly') {
                currentChart = 'monthly';
                orderChart.data = orderData[currentChart];
                orderChart.update();
                
                // Update active button
                document.getElementById('weekly-chart').classList.remove('active');
                this.classList.add('active');
            }
        });
    });
</script>
@endsection