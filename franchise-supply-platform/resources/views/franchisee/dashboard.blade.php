@extends('layouts.franchisee')

@section('title', 'Franchisee Dashboard - Restaurant Supply Platform')

@section('page-title', 'Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/status-styles.css') }}">
<style>
    /* Modern Dashboard Styles */
    .dashboard-container {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    /* Stats Cards */
    .stat-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
        height: 100%;
        overflow: hidden;
    }
    
    .stat-card:hover {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e0;
    }
    
    .stat-card .card-body {
        padding: 1.5rem;
    }
    
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin-bottom: 1rem;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 0.25rem;
        line-height: 1;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #64748b;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .stat-change {
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    /* Chart Container */
    .chart-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    
    .chart-header {
        padding: 1.5rem 1.5rem 0 1.5rem;
        border-bottom: none;
    }
    
    .chart-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.5rem;
    }
    
    .total-spending-display {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        border-radius: 8px;
        padding: 1rem;
        color: white;
        margin-bottom: 1rem;
    }
    
    .total-spending-value {
        font-size: 1.875rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .spending-period {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    .chart-controls {
        display: flex;
        gap: 0.5rem;
    }
    
    .chart-btn {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        font-size: 0.875rem;
        color: #64748b;
        transition: all 0.2s ease;
    }
    
    .chart-btn.active {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    .chart-container {
        height: 280px;
        padding: 1.5rem;
    }
    
    /* Quick Actions */
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .action-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
    }
    
    .action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: inherit;
    }
    
    .action-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin: 0 auto 1rem;
    }
    
    .action-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.5rem;
    }
    
    .action-subtitle {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    /* Content Cards */
    .content-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        margin-bottom: 2rem;
    }
    
    .content-header {
        padding: 1.5rem 1.5rem 1rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 0;
    }
    
    .content-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1a202c;
    }
    
    .view-all-btn {
        font-size: 0.875rem;
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
    }
    
    .view-all-btn:hover {
        color: #2563eb;
        text-decoration: none;
    }
    
    /* Tables */
    .modern-table {
        margin: 0;
    }
    
    .modern-table th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.875rem;
        border: none;
        padding: 1rem 1.5rem;
    }
    
    .modern-table td {
        border: none;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .modern-table tbody tr:hover {
        background: #f8fafc;
    }
    
    /* Status Badges */
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }
    
    /* Product Grid */
    .product-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        padding: 1.5rem;
    }
    
    .product-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .product-item:hover {
        border-color: #e2e8f0;
        background: #f8fafc;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 8px;
        object-fit: cover;
        margin-right: 1rem;
        background: #f1f5f9;
    }
    
    .product-info {
        flex-grow: 1;
    }
    
    .product-name {
        font-weight: 600;
        color: #1a202c;
        margin-bottom: 0.25rem;
    }
    
    .product-price {
        color: #059669;
        font-weight: 600;
    }
    
    .product-unit {
        color: #64748b;
        font-size: 0.875rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem 0;
        }
        
        .stat-value {
            font-size: 1.5rem;
        }
        
        .product-grid {
            grid-template-columns: 1fr;
        }
        
        .quick-actions-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-container">
    <div class="container-fluid">
        
        <!-- Stats Overview -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-value">${{ number_format($stats['monthly_spending'] ?? 0, 0) }}</div>
                        <div class="stat-label">Monthly Spending</div>
                        @if(isset($stats['spending_change']))
                            <div class="stat-change {{ $stats['spending_change'] > 0 ? 'text-danger' : ($stats['spending_change'] < 0 ? 'text-success' : 'text-muted') }}">
                                <i class="fas fa-{{ $stats['spending_change'] > 0 ? 'arrow-up' : ($stats['spending_change'] < 0 ? 'arrow-down' : 'minus') }}"></i>
                                {{ abs($stats['spending_change']) }}% from last month
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-value">{{ $stats['pending_orders'] ?? 0 }}</div>
                        <div class="stat-label">Pending Orders</div>
                        @if(isset($stats['pending_orders_change']))
                            <div class="stat-change {{ $stats['pending_orders_change'] > 0 ? 'text-success' : ($stats['pending_orders_change'] < 0 ? 'text-danger' : 'text-muted') }}">
                                <i class="fas fa-{{ $stats['pending_orders_change'] > 0 ? 'arrow-up' : ($stats['pending_orders_change'] < 0 ? 'arrow-down' : 'minus') }}"></i>
                                {{ abs($stats['pending_orders_change']) }}% from last month
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="stat-value">{{ $stats['incoming_deliveries'] ?? 0 }}</div>
                        <div class="stat-label">Incoming Deliveries</div>
                        <div class="stat-change text-muted">
                            Expected this week
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-value">${{ number_format(array_sum($charts['weekly_spending'] ?? [0]), 0) }}</div>
                        <div class="stat-label">Weekly Spending</div>
                        <div class="stat-change text-muted">
                            This week total
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <h5 class="mb-3 text-dark fw-semibold">Quick Actions</h5>
                <div class="quick-actions-grid">
                    <a href="{{ route('franchisee.catalog') }}" class="action-card">
                        <div class="action-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="action-title">Shop Products</div>
                        <div class="action-subtitle">Browse our catalog</div>
                    </a>
                    
                    <a href="{{ route('franchisee.orders.pending') }}" class="action-card">
                        <div class="action-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="action-title">Track Orders</div>
                        <div class="action-subtitle">Monitor shipments</div>
                    </a>
                    
                    <a href="{{ route('franchisee.orders.history') }}" class="action-card">
                        <div class="action-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="action-title">Order History</div>
                        <div class="action-subtitle">View past orders</div>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Chart Section -->
            <div class="col-lg-8 mb-4">
                <div class="chart-card">
                    <div class="chart-header">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="chart-title">Order Activity & Spending</h5>
                                <div class="total-spending-display">
                                    <div class="total-spending-value" id="total-spending-value">
                                        ${{ number_format(array_sum($charts['weekly_spending'] ?? [0, 0, 0, 0, 0, 0, 0]), 2) }}
                                    </div>
                                    <div class="spending-period" id="spending-period">This week</div>
                                </div>
                            </div>
                            <div class="chart-controls">
                                <button type="button" class="chart-btn active" id="weekly-chart">Weekly</button>
                                <button type="button" class="chart-btn" id="monthly-chart">Monthly</button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="orderSpendingChart"></canvas>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="content-card">
                    <div class="content-header">
                        <h5 class="content-title">Recent Orders</h5>
                        <a href="{{ route('franchisee.orders.pending') }}" class="view-all-btn">View All →</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table modern-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_orders as $order)
                                <tr>
                                    <td class="fw-semibold">#{{ $order->order_number ?? $order->id }}</td>
                                    <td class="text-muted">{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>{{ $order->items_count }} items</td>
                                    <td class="fw-semibold">${{ number_format($order->total, 2) }}</td>
                                    <td>
                                        @if($order->status == 'pending')
                                            <span class="status-badge bg-secondary text-white">Pending</span>
                                        @elseif($order->status == 'processing' || $order->status == 'approved')
                                            <span class="status-badge bg-info text-white">Processing</span>
                                        @elseif($order->status == 'packed')
                                            <span class="status-badge bg-warning text-white">Packed</span>
                                        @elseif($order->status == 'shipped')
                                            <span class="status-badge bg-primary text-white">Shipped</span>
                                        @elseif($order->status == 'delivered')
                                            <span class="status-badge bg-success text-white">Delivered</span>
                                        @elseif($order->status == 'cancelled' || $order->status == 'rejected')
                                            <span class="status-badge bg-danger text-white">{{ ucfirst($order->status) }}</span>
                                        @else
                                            <span class="status-badge bg-secondary text-white">{{ ucfirst($order->status) }}</span>
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
                                    <td colspan="6" class="text-center py-4 text-muted">No recent orders found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Popular Products -->
            <div class="col-lg-4">
                <div class="content-card">
                    <div class="content-header">
                        <h5 class="content-title">Popular Products</h5>
                        <a href="{{ route('franchisee.catalog', ['sort' => 'popular']) }}" class="view-all-btn">View All →</a>
                    </div>
                    <div class="product-grid">
                        @foreach($popular_products as $product)
                        <div class="product-item">
                            <div class="me-3">
                                @if($product->images && $product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                         class="product-image" alt="{{ $product->name }}">
                                @else
                                    <div class="product-image d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="product-info flex-grow-1">
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="product-price">${{ number_format($product->price, 2) }}</span>
                                    <span class="product-unit">{{ $product->unit_size }} {{ $product->unit_type }}</span>
                                </div>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-success quick-add-to-cart" 
                                        data-product-id="{{ $product->id }}"
                                        {{ (!$product->inventory_count && !$product->has_in_stock_variants) ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
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
    
    const calculateTotalSpending = (data) => {
        return data.reduce((total, value) => total + value, 0);
    };
    
    const formatCurrency = (value) => {
        return '$' + value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    };
    
    const updateTotalSpending = (view) => {
        const total = calculateTotalSpending(orderData[view].spending);
        document.getElementById('total-spending-value').textContent = formatCurrency(total);
        document.getElementById('spending-period').textContent = view === 'weekly' ? 'This week' : 'This year';
    };
    
    // Create the chart with modern styling
    const orderSpendingChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: orderData[currentView].labels,
            datasets: [
                {
                    label: 'Spending ($)',
                    data: orderData[currentView].spending,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return 'Spending: $' + context.raw.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 12
                        }
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f1f5f9',
                        borderDash: [2, 2]
                    },
                    border: {
                        display: false
                    },
                    ticks: {
                        color: '#64748b',
                        font: {
                            size: 12
                        },
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
    
    // Chart toggle buttons
    document.getElementById('weekly-chart').addEventListener('click', function() {
        if (currentView !== 'weekly') {
            currentView = 'weekly';
            
            orderSpendingChart.data.labels = orderData[currentView].labels;
            orderSpendingChart.data.datasets[0].data = orderData[currentView].spending;
            orderSpendingChart.update();
            
            updateTotalSpending(currentView);
            
            document.getElementById('monthly-chart').classList.remove('active');
            this.classList.add('active');
        }
    });
    
    document.getElementById('monthly-chart').addEventListener('click', function() {
        if (currentView !== 'monthly') {
            currentView = 'monthly';
            
            orderSpendingChart.data.labels = orderData[currentView].labels;
            orderSpendingChart.data.datasets[0].data = orderData[currentView].spending;
            orderSpendingChart.update();
            
            updateTotalSpending(currentView);
            
            document.getElementById('weekly-chart').classList.remove('active');
            this.classList.add('active');
        }
    });
    
    updateTotalSpending(currentView);
});
</script>
@endsection