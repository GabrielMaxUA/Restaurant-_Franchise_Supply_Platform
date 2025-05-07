@extends('layouts.franchisee')

@section('title', 'Franchisee Dashboard - Restaurant Supply Platform')

@section('page-title', 'Dashboard')

@section('styles')
<style>
    .stat-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
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
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-bottom: 1rem;
    }
    
    .chart-container {
        height: 250px;
        position: relative;
    }
    
    .order-activity-header {
        padding: 15px;
    }
    
    .total-spending-box {
        background-color: rgba(40, 167, 69,.1);
        border: 1px solid rgba(40, 167, 69, .2);
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 15px;
    }
    
    .total-spending-box h3 {
        margin-bottom: 5px;
        color: #28a745;
    }
    
    .dashboard-row {
        display: flex;
        margin-bottom: 20px;
    }
    
    .dashboard-column {
        padding: 0 10px;
    }
    
    .dashboard-column-left {
        width: 50%;
    }
    
    .dashboard-column-right {
        width: 50%;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
        gap: 15px;
        height: 100%;
    }
    
    .stat-block {
        height: auto;
        min-height: 115px;
    }
    
    .dashboard-card {
        height: 100%;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    @media (max-width: 991px) {
        .dashboard-row {
            flex-direction: column;
        }
        
        .dashboard-column-left, .dashboard-column-right {
            width: 100%;
        }
        
        .dashboard-column-right {
            margin-top: 20px;
        }
    }
</style>
@endsection

@section('content')
<!-- Dashboard Layout -->
<div class="dashboard-row">
    <!-- Left Column - Order Activity Chart -->
    <div class="dashboard-column dashboard-column-left">
        <div class="card dashboard-card">
            <div class="card-body">
                <div class="order-activity-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Order Activity - Total Spending</h5>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-success active" id="weekly-chart">Weekly</button>
                        <button type="button" class="btn btn-outline-success" id="monthly-chart">Monthly</button>
                    </div>
                </div>
                <div class="total-spending-box">
                    <h5>Total Spending</h5>
                    <h3 id="total-spending-value" class="mb-0">
                        ${{ number_format(array_sum($charts['weekly_spending'] ?? [0, 0, 0, 0, 0, 0, 0]), 2) }}
                    </h3>
                    <p class="text-muted mb-0" id="spending-period">This week</p>
                </div>
                <div class="chart-container">
                    <canvas id="orderSpendingChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Right Column - Stat Blocks -->
    <div class="dashboard-column dashboard-column-right">
        <div class="stats-grid">
            <!-- Monthly Spending -->
            <div class="stat-block">
                <div class="card stat-card bg-light">
                    <div class="card-body">
                        <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-dollar-sign"></i>
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
            
            <!-- Pending Orders -->
            <div class="stat-block">
                <div class="card stat-card bg-light">
                    <div class="card-body">
                        <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-shopping-cart"></i>
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
            
            <!-- Low Stock Items -->
            <div class="stat-block">
                <div class="card stat-card bg-light">
                    <div class="card-body">
                        <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
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
            
            <!-- Incoming Deliveries -->
            <div class="stat-block">
                <div class="card stat-card bg-light">
                    <div class="card-body">
                        <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-truck-loading"></i>
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
    </div>
</div>

<!-- Main Dashboard Content -->
<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
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
                            <div style="width: 60px; height: 60px;" class="me-3 d-flex align-items-center">
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
                                <button type="button" class="btn btn-sm btn-outline-success quick-add-to-cart" 
                                        data-product-id="{{ $product->id }}"
                                        {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus"></i>
                                </button>
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
@include('layouts.components.alert-component')
@include('layouts.components.add-to-cart')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Order Spending Chart
    const ctx = document.getElementById('orderSpendingChart').getContext('2d');
    const orderData = {
        weekly: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            spending: {{ json_encode($charts['weekly_spending'] ?? [0, 0, 0, 0, 0, 0, 0]) }},
            orders: {{ json_encode($charts['weekly_orders'] ?? [0, 0, 0, 0, 0, 0, 0]) }}
        },
        monthly: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            spending: {{ json_encode($charts['monthly_spending'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }},
            orders: {{ json_encode($charts['monthly_orders'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) }}
        }
    };
    
    let currentView = 'weekly';
    
    // Function to calculate total spending
    const calculateTotalSpending = (data) => {
        return data.reduce((total, value) => total + value, 0);
    };
    
    // Format currency
    const formatCurrency = (value) => {
        return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };
    
    // Update the total spending display
    const updateTotalSpending = (view) => {
        const total = calculateTotalSpending(orderData[view].spending);
        document.getElementById('total-spending-value').textContent = formatCurrency(total);
        document.getElementById('spending-period').textContent = view === 'weekly' ? 'This week' : 'This year';
    };
    
    // Create the chart
    const orderSpendingChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: orderData[currentView].labels,
            datasets: [
                {
                    label: 'Orders',
                    data: orderData[currentView].orders,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Spending ($)',
                    data: orderData[currentView].spending,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    max: 50, // Set maximum value to 50
                    title: {
                        display: true,
                        text: 'Orders',
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 10, // Set step size to 10
                        font: {
                            size: 10
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    max: 10000, // Set maximum value to 10,000
                    title: {
                        display: true,
                        text: 'Spending ($)',
                        font: {
                            size: 10
                        }
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        stepSize: 1000, // Set step size to 1,000
                        callback: function(value) {
                            return '$' + value;
                        },
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: {
                            size: 10
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Spending ($)') {
                                return 'Spending: $' + context.raw.toFixed(2);
                            }
                            return 'Orders: ' + context.raw;
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 3,
                    hoverRadius: 4
                },
                line: {
                    borderWidth: 2
                }
            }
        }
    });
    
    // Chart toggle buttons
    document.getElementById('weekly-chart').addEventListener('click', function() {
        if (currentView !== 'weekly') {
            currentView = 'weekly';
            
            orderSpendingChart.data.labels = orderData[currentView].labels;
            orderSpendingChart.data.datasets[1].data = orderData[currentView].spending;
            orderSpendingChart.data.datasets[0].data = orderData[currentView].orders;
            orderSpendingChart.update();
            
            updateTotalSpending(currentView);
            
            // Update active button
            document.getElementById('monthly-chart').classList.remove('active');
            this.classList.add('active');
        }
    });
    
    document.getElementById('monthly-chart').addEventListener('click', function() {
        if (currentView !== 'monthly') {
            currentView = 'monthly';
            
            orderSpendingChart.data.labels = orderData[currentView].labels;
            orderSpendingChart.data.datasets[1].data = orderData[currentView].spending;
            orderSpendingChart.data.datasets[0].data = orderData[currentView].orders;
            orderSpendingChart.update();
            
            updateTotalSpending(currentView);
            
            // Update active button
            document.getElementById('weekly-chart').classList.remove('active');
            this.classList.add('active');
        }
    });
    
    // Initialize total spending display
    updateTotalSpending(currentView);
});
</script>
@endsection