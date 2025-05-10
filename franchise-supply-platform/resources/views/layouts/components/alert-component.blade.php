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
        bottom: 80px;
        left: 20px;
        z-index: 9999;
        max-width: 90%;
        width: 450px;
    }
    
    .alert-float {
        position: relative;
        margin-bottom: 15px;
        padding: 15px 20px;
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        animation: slideInRight 0.5s;
        display: flex;
        align-items: center;
        transform-origin: left center;
    }
    
    .alert-success {
        background-color: #e2ebd8;
        border-radius: 0.5rem;
        border-left: 5px solid #28a745;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border-left: 5px solid #dc3545;
    }
    
    .alert-warning {
        background-color: #fff3cd;
        color: #856404;
        border-left: 5px solid #ffc107;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border-left: 5px solid #17a2b8;
    }
    
    .alert-float i {
        margin-right: 10px;
        font-size: 1.2em;
    }
    
    .alert-content {
        flex: 1;
    }
    
    .alert-title {
        font-weight: 600;
        margin-bottom: 2px;
    }
    
    .alert-details {
        opacity: 0.9;
    }
    
    .alert-float .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: none;
        border: none;
        font-size: 1rem;
        color: inherit;
        opacity: 0.7;
        cursor: pointer;
    }
    
    .alert-float .close-btn:hover {
        opacity: 1;
    }
    
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideOutLeft {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-100px);
        }
    }
    
    .slide-out {
        animation: slideOutLeft 0.5s forwards;
    }
</style>

<script>
    // Store shown messages to prevent duplicates
    const shownAlerts = new Set();
    
    // Function to create and display floating alerts with better formatting
    function showFloatingAlert(message, type, duration = 5000) {
        const alertsContainer = document.getElementById('floating-alerts-container');
        if (!alertsContainer) return;
        
        // Create unique identifier for this alert
        const alertKey = `${type}:${message}`;
        
        // Check if we've already shown this alert
        if (shownAlerts.has(alertKey)) {
            console.log('Preventing duplicate alert:', alertKey);
            return;
        }
        
        // Mark this alert as shown
        shownAlerts.add(alertKey);
        
        // Create alert element
        const alertElement = document.createElement('div');
        alertElement.className = `alert-float alert-${type}`;
        alertElement.dataset.alertKey = alertKey;
        
        // Determine icon based on type
        let icon = 'info-circle';
        let title = '';
        
        if (type === 'success') {
            icon = 'check-circle';
            title = 'Success';
        } else if (type === 'danger') {
            icon = 'exclamation-circle';
            title = 'Error';
        } else if (type === 'warning') {
            icon = 'exclamation-triangle';
            title = 'Warning';
        } else if (type === 'info') {
            icon = 'info-circle';
            title = 'Information';
        }
        
        // Initialize message parts
        let itemAddedMessage = '';
        let inventoryMessage = '';
        
        // Parse cart-related messages with better structure
        if (message.includes('added to cart')) {
            // Extract "remaining in stock" part if present
            const stockMatch = message.match(/\((\d+)\s+remaining in stock\)/);
            if (stockMatch) {
                inventoryMessage = `${stockMatch[1]} remaining in stock`;
                // Remove the stock part from the main message
                message = message.replace(stockMatch[0], '').trim();
            }
            
            // The rest is the item added message
            itemAddedMessage = message;
        } else {
            // For non-cart messages, use the whole message
            itemAddedMessage = message;
        }
        
        // Add content to the alert with proper structure
        alertElement.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <div class="alert-content">
                <div class="alert-title">${title}</div>
                <div class="alert-message">${itemAddedMessage}</div>
                ${inventoryMessage ? `<div class="alert-details">${inventoryMessage}</div>` : ''}
            </div>
            <button type="button" class="close-btn">&times;</button>
        `;
        
        // Add to the container
        alertsContainer.appendChild(alertElement);
        
        // Handle close button
        const closeBtn = alertElement.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alertElement.classList.add('slide-out');
                setTimeout(() => {
                    if (alertElement.parentNode) {
                        alertElement.parentNode.removeChild(alertElement);
                        // Remove from our tracking set
                        shownAlerts.delete(alertKey);
                    }
                }, 500);
            });
        }
        
        // Auto-dismiss after specified duration
        setTimeout(() => {
            if (alertElement.parentNode) {
                alertElement.classList.add('slide-out');
                setTimeout(() => {
                    if (alertElement.parentNode) {
                        alertElement.parentNode.removeChild(alertElement);
                        // Remove from our tracking set
                        shownAlerts.delete(alertKey);
                    }
                }, 500);
            }
        }, duration);
    }
    
    // Function for cart notification
    function showCartNotification(message, type = 'success') {
        showFloatingAlert(message, type);
    }
    
    // Check for session messages on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Remove any duplicate alerts that might already be in the DOM
        const alertContainer = document.getElementById('floating-alerts-container');
        if (alertContainer) {
            const existingAlerts = {};
            
            // Check for any existing alerts (in case they're already in the DOM)
            Array.from(alertContainer.children).forEach(alert => {
                const messageEl = alert.querySelector('.alert-message');
                if (!messageEl) return;
                
                const message = messageEl.textContent.trim();
                const type = Array.from(alert.classList)
                    .find(cls => cls.startsWith('alert-'))
                    ?.replace('alert-', '') || '';
                
                const key = `${type}:${message}`;
                
                if (existingAlerts[key]) {
                    // This is a duplicate, remove it
                    alert.remove();
                } else {
                    // First occurrence, track it
                    existingAlerts[key] = true;
                    // Also add to our Set to prevent recreating it
                    shownAlerts.add(key);
                }
            });
        }
        
        // Check for flash message in localStorage (for cart notifications across page loads)
        const savedNotification = localStorage.getItem('cartNotification');
        if (savedNotification) {
            showCartNotification(savedNotification);
            localStorage.removeItem('cartNotification');
        }
        
        // Check for session messages - only show them if they're not already displayed
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
    
    // Add improved handling for repeat order buttons
    document.addEventListener('DOMContentLoaded', function() {
        // Find all repeat order buttons
        const repeatButtons = document.querySelectorAll('.repeat-order-btn');
        
        repeatButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                // Prevent action if button is already processing
                if (this.classList.contains('processing')) {
                    e.preventDefault();
                    return false;
                }
                
                // Mark button as processing to prevent multiple clicks
                this.classList.add('processing');
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                
                // Clear any existing stored notifications to prevent duplicates
                localStorage.removeItem('cartNotification');
                
                // Add a delay before allowing click to proceed
                setTimeout(() => {
                    // Continue with normal link behavior
                }, 100);
            });
        });
    });
</script>