<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Franchise Supply Platform')</title>
    
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
            position: relative;
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
                        <h5 class="text-center">Restaurant Franchise</h5>
                        <p class="text-center mb-0">Supply Platform</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}" href="{{ url('/admin/dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/products*') ? 'active' : '' }}" href="{{ url('/admin/products') }}">
                                <i class="fas fa-box me-2"></i>
                                Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/categories*') ? 'active' : '' }}" href="{{ url('/admin/categories') }}">
                                <i class="fas fa-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" href="{{ url('/admin/orders') }}">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Orders
                                @php
                                    $pendingOrdersCount = \App\Models\Order::where('status', 'pending')->count();
                                @endphp
                                @if($pendingOrdersCount > 0)
                                    <span class="badge bg-danger ms-2">{{ $pendingOrdersCount }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ url('/admin/users') }}">
                                <i class="fas fa-users me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/quickbooks*') ? 'active' : '' }}" href="{{ url('/admin/quickbooks') }}">
                                <i class="fas fa-calculator me-2"></i>
                                QuickBooks
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
                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>
                        <div class="d-flex">
                            @include('layouts.components.notification-bell')
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i> {{ Auth::user()->username ?? Auth::user()->email ?? 'Admin' }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li>
                                      <a class="dropdown-item" href="{{ url('/admin/profile') }}">
                                        <i class="fas fa-cog me-2"></i>Settings
                                      </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ url('/logout') }}">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                      </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Order Notifications Bar -->
                @php
                    $pendingOrders = \App\Models\Order::where('status', 'pending')->count();
                    $activeOrders = \App\Models\Order::whereIn('status', ['approved', 'packed', 'shipped'])->count();
                @endphp

                @if($pendingOrders > 0 || $activeOrders > 0)
                <div class="order-notification-bar mb-3">
                    <div class="container-fluid">
                        <div class="row">
                            @if($pendingOrders > 0)
                            <div class="col-md-6 mb-2 mb-md-0">
                                <div class="alert alert-warning mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock me-2"></i>
                                        <div>
                                            <strong>{{ $pendingOrders }} {{ Str::plural('order', $pendingOrders) }}</strong> pending approval
                                            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="btn btn-sm btn-warning ms-3">
                                                View Orders
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if($activeOrders > 0)
                            <div class="col-md-{{ $pendingOrders > 0 ? '6' : '12' }}">
                                <div class="alert alert-info mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-box me-2"></i>
                                        <div>
                                            <strong>{{ $activeOrders }} {{ Str::plural('order', $activeOrders) }}</strong> in progress
                                            <a href="{{ route('admin.orders.index', ['status' => ['approved', 'packed', 'shipped']]) }}" class="btn btn-sm btn-info ms-3 text-white">
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
                    @if(!session('hide_welcome'))
                    <div class="alert alert-success persistent-guide mb-4">
                        <h4 class="alert-heading"><i class="fas fa-star me-2"></i> Welcome back, {{ Auth::user()->username ?? Auth::user()->email ?? 'Admin' }}!</h4>
                        <p class="mt-3">Platform Status: <strong>{{ \App\Models\Order::where('status', 'pending')->count() }}</strong> pending orders 
                        <p class="mt-3">Inventory Status: 
                            <strong>{{ \App\Models\Product::where('inventory_count', '<=', 10)->where('inventory_count', '>', 0)->count() + \App\Models\ProductVariant::where('inventory_count', '<=', 10)->where('inventory_count', '>', 0)->count() }}</strong> items low on inventory and 
                            <strong>{{ \App\Models\Product::where('inventory_count', '=', 0)->count() + \App\Models\ProductVariant::where('inventory_count', '=', 0)->count() }}</strong> items out of stock.
                        </p>
                        <hr>
                        <p class="mb-0">Check the dashboard for more insights about the supply platform status.</p>
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

    <!-- Custom JS for notifications -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
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
    
    <!-- Add auto-refresh for checking new orders -->
    <script>
        // Check for new orders every 30 seconds
        setInterval(function() {
            fetch('{{ url('/admin/orders/check-new') }}')
                .then(response => response.json())
                .then(data => {
                    const pendingOrdersCount = data.pending_orders_count;
                    
                    // Update the notification badge in sidebar
                    const sidebarBadge = document.querySelector('.nav-link .badge');
                    
                    if (pendingOrdersCount > 0) {
                        if (sidebarBadge) {
                            sidebarBadge.textContent = pendingOrdersCount;
                        } else {
                            const ordersLink = document.querySelector('.nav-link:has(.fa-shopping-cart)');
                            if (ordersLink) {
                                const badge = document.createElement('span');
                                badge.className = 'badge bg-danger ms-2';
                                badge.textContent = pendingOrdersCount;
                                ordersLink.appendChild(badge);
                            }
                        }
                        
                        // Update the notification bell in navbar
                        let navbarBadge = document.querySelector('.navbar .badge');
                        const navbarBell = document.querySelector('.navbar .btn-outline-success');
                        
                        if (navbarBell) {
                            if (navbarBadge) {
                                navbarBadge.textContent = pendingOrdersCount;
                            }
                        } else {
                            // Create notification bell if it doesn't exist
                            const navbar = document.querySelector('.navbar .d-flex');
                            if (navbar) {
                                const bellLink = document.createElement('a');
                                bellLink.href = '{{ url('/admin/orders?status=pending') }}';
                                bellLink.className = 'btn btn-outline-success me-2 position-relative';
                                bellLink.innerHTML = `
                                    <i class="fas fa-bell"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        ${pendingOrdersCount}
                                    </span>
                                `;
                                navbar.prepend(bellLink);
                            }
                        }
                    } else {
                        // Remove badges if no pending orders
                        if (sidebarBadge) {
                            sidebarBadge.remove();
                        }
                        
                        const navbarBell = document.querySelector('.navbar .btn-outline-success');
                        if (navbarBell) {
                            navbarBell.remove();
                        }
                    }
                })
                .catch(error => console.error('Error checking for new orders:', error));
        }, 30000);
    </script>
    
    <!-- Optional JavaScript -->
    @yield('scripts')
    
    <!-- Loading Overlay Script -->
    <script src="{{ asset('js/loading-overlay.js') }}"></script>
</body>
</html>