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
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
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
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>

<style>
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
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ url('/admin/users') }}">
                                <i class="fas fa-users me-2"></i>
                                Users
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
                        <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>
                        <div class="d-flex">
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-user me-2"></i> {{ Auth::user()->username ?? 'Admin' }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><a class="dropdown-item" href="{{ url('/admin/profile') }}">Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ url('/logout') }}">Logout</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <div class="container-fluid">  
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Optional JavaScript -->
    @yield('scripts')

        <!-- Add the auto-dismiss script RIGHT HERE, after the @yield('scripts') -->
        <script>
        // Auto-dismiss alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            
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
        alertElement.className = `alert alert-${type}  fade show alert-float`;
        alertElement.innerHTML = `
            ${message}
          
        `;
        
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
</body>
</html>