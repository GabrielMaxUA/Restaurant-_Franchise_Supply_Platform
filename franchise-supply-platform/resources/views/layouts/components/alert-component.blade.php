{{-- 
    Reusable floating alert component for all user types
    Place this file in resources/views/components/alert-component.blade.php
--}}

<!-- Floating Alerts Container -->
<div id="floating-alerts-container"></div>

<style>
    /* Standardized floating alert styling across all user types */
    #floating-alerts-container {
        position: fixed;
        z-index: 9999;
    }
    
    .alert-float {
            position: fixed;
            top: 47px;
            left: 75%;
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
            transform: translate(-50%, -20px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, 0);
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

<script>
    // Function to create and display floating alerts
    function showFloatingAlert(message, type) {
        const alertsContainer = document.getElementById('floating-alerts-container');
        
        // Create alert element
        const alertElement = document.createElement('div');
        alertElement.className = `alert alert-${type} fade show alert-float`;
        
        // Create alert content with icon based on type
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'danger') icon = 'exclamation-circle';
        if (type === 'warning') icon = 'exclamation-triangle';
        
        // Add content to the alert with proper structure
        alertElement.innerHTML = `
            <div class="d-flex justify-content-between align-items-center w-100">
                <div>
                    <i class="fas fa-${icon} me-2"></i>
                    ${message}
                </div>
            </div>
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
    
    // Special function for cart notification
    function showCartNotification(message) {
        showFloatingAlert(message, 'success');
    }
    
    // Check for session messages on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check for flash message in localStorage (for cart notifications across page loads)
        const savedNotification = localStorage.getItem('cartNotification');
        if (savedNotification) {
            showCartNotification(savedNotification);
            localStorage.removeItem('cartNotification');
        }
        
        // Check for session messages
        @if(session('success'))
            showFloatingAlert("{{ session('success') }}", "success");
        @endif
        
        @if(session('error'))
            showFloatingAlert("{{ session('error') }}", "danger");
        @endif
        
        @if(session('warning'))
            showFloatingAlert("{{ session('warning') }}", "warning");
        @endif
        
        @if(session('info'))
            showFloatingAlert("{{ session('info') }}", "info");
        @endif
        
        // Setup cart form listeners for add to cart notifications
        setupCartNotifications();
    });
    
    // Handle cart form submissions
    function setupCartNotifications() {
        // Select all add to cart forms
        const addToCartForms = document.querySelectorAll('form[action*="cart.add"]');
        
        addToCartForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                // For traditional form submission, store message to show after page reload
                if (!e.defaultPrevented) {
                    // Try to get product name from closest container
                    const productContainer = form.closest('tr, .col-md-6, div');
                    let productName = "Product";
                    
                    if (productContainer) {
                        const nameElement = productContainer.querySelector('h5, h6, .card-title');
                        if (nameElement) {
                            productName = nameElement.textContent.trim();
                        }
                    }
                    
                    localStorage.setItem('cartNotification', productName + ' added to cart successfully.');
                }
            });
        });
    }
</script>