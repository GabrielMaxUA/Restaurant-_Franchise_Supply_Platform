@extends('layouts.franchisee')

@section('title', 'Order Reports - Franchisee Portal')

@section('page-title', 'Order Reports')

@section('styles')
<style>
    .stats-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: transform 0.2s;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-top: 20px;
    }
    
    .product-rank {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
    }
    
    .rank-1 { background-color: #ffd700; color: #000; }
    .rank-2 { background-color: #c0c0c0; color: #000; }
    .rank-3 { background-color: #cd7f32; color: #fff; }
    .rank-other { background-color: #6c757d; color: #fff; }
    
    /* Toggle button styles */
    .btn-group-sm .btn {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }
    
    .btn-outline-primary {
        color: #3b82f6;
        border-color: #3b82f6;
    }
    
    .btn-outline-primary:hover {
        color: #fff;
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    
    .btn-outline-primary.active {
        color: #fff;
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
</style>
@endsection

@section('content')
<!-- Date Range Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('franchisee.orders.reports') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" 
                       value="{{ $startDate->format('Y-m-d') }}" max="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" 
                       value="{{ $endDate->format('Y-m-d') }}" max="{{ date('Y-m-d') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Update Report
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary Statistics -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-primary text-white me-3">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Orders</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_orders']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-success text-white me-3">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Spent</h6>
                        <h3 class="mb-0">${{ number_format($stats['total_spent'], 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-info text-white me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Avg Order Value</h6>
                        <h3 class="mb-0">${{ number_format($stats['avg_order_value'] ?? 0, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stats-icon bg-warning text-white me-3">
                        <i class="fas fa-box"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Items</h6>
                        <h3 class="mb-0">{{ number_format($stats['total_items']) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Order Status Breakdown -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Orders by Status</h5>
            </div>
            <div class="card-body">
                @if($ordersByStatus->isEmpty())
                    <p class="text-muted text-center">No orders in the selected period</p>
                @else
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Spending Trends with Toggle -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Spending Trends</h5>
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-primary active" id="weekly-chart">Weekly</button>
                    <button type="button" class="btn btn-outline-primary" id="monthly-chart">Monthly</button>
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h3 class="mb-0" id="total-spending-value">$0.00</h3>
                    <small class="text-muted" id="spending-period">This week</small>
                </div>
                <div class="chart-container">
                    <canvas id="spendingChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Most Ordered Products -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Top 10 Most Ordered Products</h5>
        <span class="text-muted">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</span>
    </div>
    <div class="card-body">
        @if($productStats->isEmpty())
            <p class="text-muted text-center">No products ordered in the selected period</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">Rank</th>
                            <th>Product Name</th>
                            <th class="text-center">Times Ordered</th>
                            <th class="text-center">Total Quantity</th>
                            <th class="text-end">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productStats as $index => $product)
                        <tr>
                            <td>
                                <span class="product-rank {{ $index < 3 ? 'rank-' . ($index + 1) : 'rank-other' }}">
                                    {{ $index + 1 }}
                                </span>
                            </td>
                            <td>{{ $product->name }}</td>
                            <td class="text-center">{{ $product->order_count }}</td>
                            <td class="text-center">{{ $product->total_quantity }}</td>
                            <td class="text-end">${{ number_format($product->total_revenue, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Export Options -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Export Options</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Export Current Report Data</h6>
                <p class="text-muted">Export the data shown above for the selected date range</p>
                <form action="{{ route('franchisee.orders.export') }}" method="GET" class="d-inline export-form" data-no-loading>
                    <input type="hidden" name="date_from" value="{{ $startDate->format('Y-m-d') }}">
                    <input type="hidden" name="date_to" value="{{ $endDate->format('Y-m-d') }}">
                    <input type="hidden" name="format" value="csv">
                    <button type="submit" class="btn btn-success download-btn">
                        <i class="fas fa-file-csv me-2"></i>Download CSV
                    </button>
                </form>
            </div>
            <div class="col-md-6">
                <h6>Export All Order History</h6>
                <p class="text-muted">Export your complete order history without date filters</p>
                <a href="{{ route('franchisee.orders.export') }}?format=csv" class="btn btn-outline-success download-link">
                    <i class="fas fa-download me-2"></i>Download All Orders
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Handle export form submissions to prevent loading overlay from staying
document.addEventListener('DOMContentLoaded', function() {
    // Handle export forms
    document.querySelectorAll('.export-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show loading overlay with custom message
            if (typeof window.showLoadingOverlay === 'function') {
                window.showLoadingOverlay('Preparing download...');
            }
            
            // Submit form in a way that allows download
            const url = this.action + '?' + new URLSearchParams(new FormData(this)).toString();
            
            // Create temporary iframe for download
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            iframe.src = url;
            
            // Hide loading overlay after a short delay
            setTimeout(() => {
                if (typeof window.hideLoadingOverlay === 'function') {
                    window.hideLoadingOverlay();
                }
                // Clean up iframe
                document.body.removeChild(iframe);
            }, 2000);
        });
    });
    
    // Handle direct download links
    document.querySelectorAll('.download-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading overlay
            if (typeof window.showLoadingOverlay === 'function') {
                window.showLoadingOverlay('Preparing download...');
            }
            
            // Trigger download
            const url = this.href;
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            document.body.appendChild(iframe);
            iframe.src = url;
            
            // Hide loading overlay after a short delay
            setTimeout(() => {
                if (typeof window.hideLoadingOverlay === 'function') {
                    window.hideLoadingOverlay();
                }
                // Clean up iframe
                document.body.removeChild(iframe);
            }, 2000);
        });
    });
});

// Chart initialization code
document.addEventListener('DOMContentLoaded', function() {
    // Order Status Chart
    @if(!$ordersByStatus->isEmpty())
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($ordersByStatus->keys()->map(fn($s) => ucfirst($s))->values()) !!},
            datasets: [{
                data: {!! json_encode($ordersByStatus->values()) !!},
                backgroundColor: [
                    '#28a745', // delivered
                    '#dc3545', // rejected
                    '#ffc107', // pending
                    '#17a2b8', // processing/approved
                    '#6c757d', // packed
                    '#007bff', // shipped
                    '#e83e8c'  // cancelled
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
    @endif
    
    // Spending Chart with Weekly/Monthly Toggle
    @if(isset($chartData))
    const spendingCtx = document.getElementById('spendingChart').getContext('2d');
    
    const spendingData = {
        weekly: {
            labels: {!! json_encode($chartData['weekly']['labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
            spending: {!! json_encode($chartData['weekly']['spending'] ?? [0, 0, 0, 0, 0, 0, 0]) !!},
            orders: {!! json_encode($chartData['weekly']['orders'] ?? [0, 0, 0, 0, 0, 0, 0]) !!}
        },
        monthly: {
            labels: {!! json_encode($chartData['monthly']['labels'] ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
            spending: {!! json_encode($chartData['monthly']['spending'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!},
            orders: {!! json_encode($chartData['monthly']['orders'] ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]) !!}
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
        const total = calculateTotalSpending(spendingData[view].spending);
        document.getElementById('total-spending-value').textContent = formatCurrency(total);
        document.getElementById('spending-period').textContent = view === 'weekly' ? 'This week' : 'This year';
    };
    
    // Initialize total spending
    updateTotalSpending(currentView);
    
    const spendingChart = new Chart(spendingCtx, {
        type: 'line',
        data: {
            labels: spendingData[currentView].labels,
            datasets: [{
                label: 'Spending ($)',
                data: spendingData[currentView].spending,
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
            }]
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
    
    // Toggle buttons
    document.getElementById('weekly-chart').addEventListener('click', function() {
        if (currentView !== 'weekly') {
            currentView = 'weekly';
            
            spendingChart.data.labels = spendingData[currentView].labels;
            spendingChart.data.datasets[0].data = spendingData[currentView].spending;
            spendingChart.update();
            
            updateTotalSpending(currentView);
            
            document.getElementById('monthly-chart').classList.remove('active');
            this.classList.add('active');
        }
    });
    
    document.getElementById('monthly-chart').addEventListener('click', function() {
        if (currentView !== 'monthly') {
            currentView = 'monthly';
            
            spendingChart.data.labels = spendingData[currentView].labels;
            spendingChart.data.datasets[0].data = spendingData[currentView].spending;
            spendingChart.update();
            
            updateTotalSpending(currentView);
            
            document.getElementById('weekly-chart').classList.remove('active');
            this.classList.add('active');
        }
    });
    @endif
});
</script>
@endsection