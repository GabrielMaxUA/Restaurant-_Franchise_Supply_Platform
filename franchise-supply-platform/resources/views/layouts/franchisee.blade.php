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
            background-color:rgb(29, 30, 29);
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
        }
        .sidebar .nav-link:hover {
            color: rgba(255, 255, 255, 1);
            background-color: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link.active {
            color: white;
            font-weight: bold;
            background-color: rgba(255, 255, 255, 0.2);
        }
        .main-content {
            padding: 20px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        
        /* Order status badges */
        .badge-pending {
            background-color: #ffc107;
            color: #212529;
        }
        .badge-processing {
            background-color: #17a2b8;
            color: white;
        }
        .badge-shipped {
            background-color: #007bff;
            color: white;
        }
        .badge-delivered {
            background-color: #28a745;
            color: white;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: white;
        }
        
        /* Product card styling */
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        /* Category pills */
        .category-pill {
            display: inline-block;
            padding: 8px 15px;
            margin: 5px;
            border-radius: 20px;
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 500;
            transition: all 0.3s;
        }
        .category-pill:hover, .category-pill.active {
            background-color: #28a745;
            color: white;
            text-decoration: none;
        }
        
        /* Persistent guide styling */
        .persistent-guide {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            border-left: 3px solidrgb(45, 49, 46);
        }
        .persistent-guide.fade {
            transition: none !important;
        }
        .persistent-guide .close,
        .persistent-guide .btn-close {
            display: none !important;
        }
        
        /* Quantity selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-selector button {
            width: 30px;
            height: 30px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-selector input {
            width: 50px;
            text-align: center;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin: 0 5px;
        }
        
        /* Order history item */
        .order-item {
            transition: background-color 0.3s;
        }
        .order-item:hover {
            background-color: rgba(40, 167, 69, 0.05);
        }
        
        /* Floating alerts */
        .alert-float {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            padding: 0.5rem 1rem;
            max-width: 500px;
            text-align: center;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: fadeInDown 0.5s;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translate3d(-50%, -20px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(-50%, 0, 0);
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
        
        .fade-out {
            animation: fadeOut 0.5s forwards;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
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
                            <a class="nav-link {{ request()->is('franchisee/cart*') ? 'active' : '' }}" href="{{ url('/franchisee/cart') }}">
                                <i class="fas fa-shopping-basket me-2"></i>
                                Cart
                                @if(session('cart') && count(session('cart')) > 0)
                                <span class="badge bg-danger ms-2">{{ count(session('cart')) }}</span>
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
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('franchisee/inventory*') ? 'active' : '' }}" href="{{ url('/franchisee/inventory') }}">
                                <i class="fas fa-cubes me-2"></i>
                                My Inventory
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
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto main-content">
                <!-- Top Navigation -->
                <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
                    <div class="container-fluid">
                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Franchisee Dashboard')</span>
                        <div class="d-flex">
                            <a href="{{ url('/franchisee/cart') }}" class="btn btn-outline-success me-2 position-relative">
                                <i class="fas fa-shopping-cart"></i>
                                @if(session('cart') && count(session('cart')) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ count(session('cart')) }}
                                </span>
                                @endif
                            </a>
                            
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user-circle me-2"></i> {{ Auth::user()->name ?? 'Franchisee' }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <!-- <li><a class="dropdown-item" href="{{ url('/franchisee/profile') }}">
                                        <i class="fas fa-user me-2"></i> Profile
                                    </a></li> -->
                                    <li><a class="dropdown-item" href="{{ url('/franchisee/settings') }}">
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
                      <i class="fas fa-star me-2">
                      </i> Welcome back, {{ session('user_name') ?? Auth::user()->email ?? 'Franchisee' }}!
                    </h4>
                      <p>You have <strong>{{ session('pending_orders') ?? 0 }}</strong> pending orders and <strong>{{ session('low_stock_items') ?? 0 }}</strong> items running low on inventory.</p>
                      <hr>
                      <p class="mb-0">Check the dashboard for more insights about your restaurant supply status.</p>
                  </div>
                  @endif
                </div>
    @yield('content')
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Optional JavaScript -->
    @yield('scripts')

    <!-- Auto-dismiss alerts script -->
    <script>
        // Auto-dismiss alerts after 5 seconds (except persistent guides)
        document.addEventListener('DOMContentLoaded', function() {
            // Only select alerts that are NOT persistent guides
            const alerts = document.querySelectorAll('.alert:not(.persistent-guide)');
            
            alerts.forEach(function(alert) {
                // Set timeout to start fade out after 4.5 seconds
                setTimeout(function() {
                    alert.classList.add('fade-out');
                }, 4500);
                
                // Set timeout to remove alert after animation completes (5 seconds total)
                setTimeout(function() {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 5000);
            });
        });
    </script>

    <!-- Floating Alerts Container -->
    <div id="floating-alerts-container"></div>

    <script>
        // Function to create and display floating alerts
        function showFloatingAlert(message, type) {
            const alertsContainer = document.getElementById('floating-alerts-container');
            
            // Create alert element
            const alertElement = document.createElement('div');
            alertElement.className = `alert alert-${type} fade show alert-float`;
            alertElement.innerHTML = message;
            
            // Add to the container
            alertsContainer.appendChild(alertElement);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alertElement.classList.add('fade-out');
                setTimeout(() => {
                    if (alertElement.parentNode) {
                        alertElement.parentNode.removeChild(alertElement);
                    }
                }, 500);
            }, 4500);
        }
        
        // Check for session messages on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check for success message
            @if(session('success'))
                showFloatingAlert("{{ session('success') }}", "success");
            @endif
            
            // Check for error message
            @if(session('error'))
                showFloatingAlert("{{ session('error') }}", "danger");
            @endif
            
            // Check for warning message
            @if(session('warning'))
                showFloatingAlert("{{ session('warning') }}", "warning");
            @endif
            
            // Check for info message
            @if(session('info'))
                showFloatingAlert("{{ session('info') }}", "info");
            @endif
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
</body>
</html>