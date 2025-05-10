<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Franchisee Portal - Restaurant Supply Platform')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
                        <h5 class="text-center">Restaurant Franchise</h5>
                        <p class="text-center mb-0">Franchisee Portal</p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('franchisee/dashboard*') ? 'active' : '' }}" href="{{ url('/franchisee/dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('franchisee/catalog*') ? 'active' : '' }}" href="{{ url('/franchisee/catalog') }}">
                                <i class="fas fa-box me-2"></i>
                                Product Catalog
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('franchisee/cart*') ? 'active' : '' }}" href="{{ url('/franchisee/cart') }}" id="sidebar-cart-link">
                                <i class="fas fa-shopping-basket me-2"></i>
                                Cart
                                @php
                                    $cart = Auth::user()->cart ?? null;
                                    // Count DISTINCT product_ids, not cart items
                                    $distinctProductCount = 0;
                                    if ($cart) {
                                        $distinctProductCount = DB::table('cart_items')
                                            ->where('cart_id', $cart->id)
                                            ->distinct('product_id')
                                            ->count('product_id');
                                    }
                                @endphp
                                @if($distinctProductCount > 0)
                                <span class="badge bg-danger ms-2 cart-sidebar-count">{{ $distinctProductCount }}</span>
                                @endif
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('franchisee/orders/pending*') ? 'active' : '' }}" href="{{ url('/franchisee/orders/pending') }}">
                                <i class="fas fa-clock me-2"></i>
                                Pending Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('franchisee/orders/history*') ? 'active' : '' }}" href="{{ url('/franchisee/orders/history') }}">
                                <i class="fas fa-history me-2"></i>
                                Order History
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
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Franchisee Dashboard')</span>
                        <div class="d-flex">
                        <a href="{{ url('/franchisee/cart') }}" class="btn btn-outline-success me-2 position-relative cart-btn" id="top-cart-btn">
                            <i class="fas fa-shopping-cart"></i>
                            @php
                                // No need to query again, we already have $distinctProductCount from above
                            @endphp
                            @if($distinctProductCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count">
                                {{ $distinctProductCount }}
                            </span>
                            @endif
                        </a>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i> {{ Auth::user()->username ?? 'Franchisee' }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                   
                                    <li><a class="dropdown-item" href="{{ url('/franchisee/profile') }}">
                                        <i class="fas fa-cog me-2"></i> Settings
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
                <!-- Page Content -->
                  <div class="container-fluid">
                    @if(session('welcome_back') && !session('hide_welcome'))
                    <div class="alert alert-success persistent-guide mb-4">
                        <h4 class="alert-heading">
                            <i class="fas fa-star me-2"></i> Welcome back, {{ session('user_name') ?? Auth::user()->username ?? 'Franchisee' }}!
                        </h4>
                        <p class="mt-3">Nice to see you back!</p>

                        <!-- @php
                          // Check if there are any recent orders with non-completed statuses
                          $pendingOrders = App\Models\Order::where('user_id', Auth::id())
                            ->whereIn('status', ['pending', 'processing', 'shipped', 'out_for_delivery', 'delayed', 'rejected', 'cancelled'])
                            ->where('updated_at', '>=', \Carbon\Carbon::now()->subDays(7))
                            ->exists();
                        @endphp

                        @if($pendingOrders)
                            <div class="mt-2">
                                <p><i class="fas fa-bell me-2"></i> <strong>You have order updates!</strong> Check your orders for the latest status changes.</p>
                            </div>
                        @endif

                        <hr> -->
                        <p class="mb-0">Check the dashboard for more insights about your restaurant supply status.</p>
                    </div>
                    @endif
                  </div>
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Include the alert component -->
    @include('layouts.components.alert-component')
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global cart update script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global function to update all cart count badges
            window.updateAllCartCountBadges = function(count) {
                // Update top navigation cart badge
                const topNavBadge = document.querySelector('#top-cart-btn .badge');
                if (topNavBadge) {
                    if (count > 0) {
                        topNavBadge.textContent = count;
                        topNavBadge.style.display = '';
                    } else {
                        topNavBadge.style.display = 'none';
                    }
                } else if (count > 0) {
                    const cartBtn = document.querySelector('#top-cart-btn');
                    if (cartBtn) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count';
                        newBadge.textContent = count;
                        cartBtn.appendChild(newBadge);
                    }
                }
                
                // Update sidebar cart badge
                const sidebarBadge = document.querySelector('#sidebar-cart-link .badge');
                if (sidebarBadge) {
                    if (count > 0) {
                        sidebarBadge.textContent = count;
                        sidebarBadge.style.display = '';
                    } else {
                        sidebarBadge.style.display = 'none';
                    }
                } else if (count > 0) {
                    const sidebarLink = document.querySelector('#sidebar-cart-link');
                    if (sidebarLink) {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'badge bg-danger ms-2 cart-sidebar-count';
                        newBadge.textContent = count;
                        sidebarLink.appendChild(newBadge);
                    }
                }
            };
            
            // Listen for custom cart update events 
            document.addEventListener('cartUpdated', function(e) {
                if (e.detail && typeof e.detail.count !== 'undefined') {
                    window.updateAllCartCountBadges(e.detail.count);
                }
            });
        });
    </script>
    
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
    
    <!-- Quantity selector script -->
    <script>
        // Handle quantity selector buttons
        document.addEventListener('DOMContentLoaded', function() {
            const decrementButtons = document.querySelectorAll('.quantity-decrement');
            const incrementButtons = document.querySelectorAll('.quantity-increment');
            
            decrementButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentNode.querySelector('input');
                    const currentValue = parseInt(input.value);
                    if (currentValue > parseInt(input.min)) {
                        input.value = currentValue - 1;
                        // Trigger change event to update any listeners
                        input.dispatchEvent(new Event('change'));
                    }
                });
            });
            
            incrementButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.parentNode.querySelector('input');
                    const currentValue = parseInt(input.value);
                    if (currentValue < parseInt(input.max)) {
                        input.value = currentValue + 1;
                        // Trigger change event to update any listeners
                        input.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
    </script>
    
    <!-- Optional JavaScript -->
    @yield('scripts')
</body>
</html>