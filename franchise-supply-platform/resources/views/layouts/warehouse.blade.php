<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Warehouse Portal - Restaurant Franchise Supply Platform')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Shared styles -->
    <link rel="stylesheet" href="{{ asset('css/notification.css') }}">
    <link rel="stylesheet" href="{{ asset('css/filters.css') }}">
    <link rel="stylesheet" href="{{ asset('css/loading-overlay.css') }}">
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            position: relative;
            transition: transform 1s ease-in-out, margin-left 1s ease-in-out, width 1s ease-in-out;
            width: 16.66667%; /* col-md-2 width */
            z-index: 100;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 1);
        }
        
        .sidebar .nav-link.active {
            color: white;
            font-weight: bold;
        }
        
        .main-content {
            padding: 20px;
            transition: margin-left 1s ease-in-out, width 1s ease-in-out;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
        
        /* Sidebar toggle button styling - now always visible */
        .sidebar-toggle {
            position: fixed;
            left: 16.66667%; /* Aligns with sidebar width */
            margin-left: -15px; /* Half the button width */
            top: 55px;
            width: 30px;
            height: 30px;
            background-color: #343a40;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            border: 2px solid #6c757d;
            z-index: 9999; /* Extremely high z-index to ensure visibility */
            transition: left 1s ease-in-out, transform 0.3s;
        }
        
        .sidebar-toggle:hover {
            background-color: #212529;
        }
        
        /* When sidebar is collapsed */
        .sidebar-collapsed {
            transform: translateX(-100%);
            margin-left: -16.66667%;
        }
        
        .content-expanded {
            margin-left: 0;
            width: 100%;
        }
        
        /* Rotate icon when sidebar is toggled */
        .icon-rotate {
            transform: rotate(180deg);
            transition: transform 0.5s;
        }
        
        /* Persistent guide styling */
        .persistent-guide {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            border-left: 3px solid #0d6efd;
        }

        .persistent-guide.fade {
            transition: none !important;
        }

        .persistent-guide .close,
        .persistent-guide .btn-close {
            display: none !important;
        }
        
        /* Card hover effects */
        .card {
            transition: transform .2s;
        }
        
        a:hover .card {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Left border styling for cards and alerts */
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
        
        /* Navbar styling */
        .navbar{
          padding-left: 20px;
        }

        .navbar-light{
          margin-left: 12px !important;
          margin-right: 12px !important;
          background-color:rgba(192, 217, 243, 0.49) !important;
          border-radius: 5px !important;
          font-size: 3em !important;
        }

        .navbar-brand{
          font-size: 1.2em !important;
        }
        
        .dropdown {
          position: relative;
          display: flex;
          align-items: center;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div id="sidebar" class="col-md-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="py-4 px-3 mb-4">
                        <h5 class="text-center">Warehouse Portal</h5>
                        <p class="text-center mb-0">Supply Management</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/dashboard*') ? 'active' : '' }}" href="{{ url('/warehouse/dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>

                        <!-- Order Management Section -->
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/orders*') ? 'active' : '' }}" href="{{ url('/warehouse/orders') }}">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Order Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/orders/pending*') ? 'active' : '' }}" href="{{ url('/warehouse/orders/pending') }}">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Awaiting Fulfillment
                                @php
                                    $approvedOrdersCount = \App\Models\Order::where('status', 'approved')->count();
                                @endphp
                                @if($approvedOrdersCount > 0)
                                    <span class="badge bg-primary ms-2">{{ $approvedOrdersCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/orders/in-progress*') ? 'active' : '' }}" href="{{ url('/warehouse/orders/in-progress') }}">
                                <i class="fas fa-box me-2"></i>
                                In Progress
                            </a>
                        </li>

                        <!-- Product Management Section -->
                        <li class="nav-item mt-3">
                            <a class="nav-link {{ request()->is('warehouse/products*') ? 'active' : '' }}" href="{{ url('/warehouse/products') }}">
                                <i class="fas fa-boxes me-2"></i>
                                Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/categories*') ? 'active' : '' }}" href="{{ url('/warehouse/categories') }}">
                                <i class="fas fa-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/inventory/low-stock*') ? 'active' : '' }}" href="{{ url('/warehouse/inventory/low-stock') }}">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Low Stock
                                @php
                                    $lowStockCount = \App\Models\Product::where('inventory_count', '<=', 10)
                                        ->where('inventory_count', '>', 0)->count() +
                                        \App\Models\ProductVariant::where('inventory_count', '<=', 10)
                                        ->where('inventory_count', '>', 0)->count();
                                @endphp
                                @if($lowStockCount > 0)
                                    <span class="badge bg-warning ms-2">{{ $lowStockCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warehouse/inventory/out-of-stock*') ? 'active' : '' }}" href="{{ url('/warehouse/inventory/out-of-stock') }}">
                                <i class="fas fa-ban me-2"></i>
                                Out of Stock
                                @php
                                    $outOfStockCount = \App\Models\Product::where('inventory_count', '=', 0)->count() +
                                        \App\Models\ProductVariant::where('inventory_count', '=', 0)->count();
                                @endphp
                                @if($outOfStockCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $outOfStockCount }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                    
                    <hr>
                    <div class="px-3 mt-4">
                        <a href="{{ url('/logout') }}" class="btn btn-danger w-100">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Toggle button as a separate element outside the sidebar -->
            <div id="sidebar-toggle" class="sidebar-toggle">
                <i id="toggle-icon" class="fas fa-chevron-left"></i>
            </div>
            
            <!-- Main Content -->
            <div id="main-content" class="col-md-10 ms-sm-auto main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Warehouse Dashboard')</span>
                        <div class="d-flex">
                            @include('layouts.components.notification-bell')
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i> {{ Auth::user()->username ?? 'Warehouse Manager' }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="{{ url('/warehouse/profile') }}">
                                    <i class="fas fa-cog me-2"></i>Settings</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ url('/logout') }}">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Order Notifications Bar -->
                @php
                    $approvedOrders = \App\Models\Order::where('status', 'approved')->count();
                    $inProgressOrders = \App\Models\Order::whereIn('status', ['packed', 'shipped'])->count();
                @endphp

                @if($approvedOrders > 0 || $inProgressOrders > 0)
                <div class="order-notification-bar mb-3">
                    <div class="container-fluid">
                        <div class="row">
                            @if($approvedOrders > 0)
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="alert alert-primary mb-0 border-left-primary">
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
                            </div>
                            @endif

                            @if($inProgressOrders > 0)
                            <div class="col-md-{{ $approvedOrders > 0 ? '6' : '12' }}">
                                <div class="alert alert-info mb-0 border-left-info">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-box me-2"></i>
                                        <div>
                                            <strong>{{ $inProgressOrders }} {{ Str::plural('order', $inProgressOrders) }}</strong> in progress
                                            <a href="{{ route('warehouse.orders.in-progress') }}" class="btn btn-sm btn-info ms-3 text-white">
                                                View Orders
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Page Content -->
                <div class="container-fluid">
                    <!-- Welcome banner -->
                    @if(!session('hide_welcome'))
                    <div class="alert {{ ((\App\Models\Product::where('inventory_count', '<=', 10)->count() > 0 || \App\Models\Product::where('inventory_count', '=', 0)->count() > 0) || (\App\Models\ProductVariant::where('inventory_count', '<=', 10)->count() > 0 || \App\Models\ProductVariant::where('inventory_count', '=', 0)->count() > 0)) ? 'alert-danger' : 'alert-success' }} persistent-guide mb-4">
                        <h4 class="alert-heading"><i class="fas fa-star me-2"></i> Welcome back, {{ Auth::user()->username ?? 'Warehouse Manager' }}!</h4>
                        <p class="mt-3">Inventory Status: 
                            <strong>{{ \App\Models\Product::where('inventory_count', '<=', 10)->where('inventory_count', '>', 0)->count() + \App\Models\ProductVariant::where('inventory_count', '<=', 10)->where('inventory_count', '>', 0)->count() }}</strong> items low on inventory and 
                            <strong>{{ \App\Models\Product::where('inventory_count', '=', 0)->count() + \App\Models\ProductVariant::where('inventory_count', '=', 0)->count() }}</strong> items out of stock.
                        </p>
                        <hr>
                        <p class="mb-0">Check the dashboard for more insights about the inventory management status.</p>
                    </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include the alert component -->
    @include('layouts.components.alert-component')
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    

    
    <!-- Enhanced Sidebar toggle script with fixes -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggleBtn = document.getElementById('sidebar-toggle');
            const toggleIcon = document.getElementById('toggle-icon');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            // Check if sidebar state is stored in localStorage
            // Default to NOT collapsed (sidebar visible by default)
            const sidebarCollapsed = localStorage.getItem('sidebar-collapsed') === 'true';
            
            // Apply initial state ONLY if explicitly collapsed
            if (sidebarCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                mainContent.classList.add('content-expanded');
                mainContent.classList.remove('col-md-10', 'ms-sm-auto');
                // Use right-facing arrow immediately when collapsed
                toggleIcon.classList.remove('fa-chevron-left');
                toggleIcon.classList.add('fa-chevron-right');
                // Position toggle button at edge of screen when sidebar is collapsed
                sidebarToggleBtn.style.left = '15px';
            }
            
            // Toggle sidebar when button is clicked
            sidebarToggleBtn.addEventListener('click', function() {
                // Toggle collapse class
                sidebar.classList.toggle('sidebar-collapsed');
                
                if (sidebar.classList.contains('sidebar-collapsed')) {
                    // Sidebar is now being hidden - immediately show right arrow
                    toggleIcon.classList.remove('fa-chevron-left');
                    toggleIcon.classList.add('fa-chevron-right');
                    
                    // Adjust content and button position
                    mainContent.classList.add('content-expanded');
                    mainContent.classList.remove('col-md-10', 'ms-sm-auto');
                    sidebarToggleBtn.style.left = '15px';
                    localStorage.setItem('sidebar-collapsed', 'true');
                } else {
                    // Sidebar is now being shown
                    // Keep right arrow until sidebar is fully visible
                    
                    // Adjust content and button position immediately
                    mainContent.classList.remove('content-expanded');
                    mainContent.classList.add('col-md-10', 'ms-sm-auto');
                    sidebarToggleBtn.style.left = '16.66667%';
                    localStorage.setItem('sidebar-collapsed', 'false');
                    
                    // Wait for animation to complete before changing icon
                    setTimeout(() => {
                        toggleIcon.classList.remove('fa-chevron-right');
                        toggleIcon.classList.add('fa-chevron-left');
                    }, 1000); // Match the 1s animation time
                }
            });
        });
    </script>
    
    <!-- Optional JavaScript -->
    @yield('scripts')
    
    <!-- Push stacked scripts -->
    @stack('scripts')
    
    <!-- Loading Overlay Script -->
    <script src="{{ asset('js/loading-overlay.js') }}"></script>
</body>
</html>