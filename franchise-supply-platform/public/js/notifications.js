/**
 * Notification system JavaScript
 */

// Function to update the notification count
function updateNotificationCount() {
    fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (data.count > 0) {
                // Create badge if it doesn't exist
                if (!badge) {
                    const bell = document.querySelector('#notificationDropdown');
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge rounded-pill bg-danger notification-badge';
                    newBadge.textContent = data.count;
                    bell.appendChild(newBadge);
                } else {
                    // Update existing badge
                    badge.textContent = data.count;
                    badge.style.display = 'block';
                }
            } else if (badge) {
                // Hide badge if count is 0
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error fetching notification count:', error));
}

// Function to load recent notifications
function loadRecentNotifications() {
    fetch('/notifications/recent')
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('.notification-list');
            if (!container) return;
            
            // Clear existing notifications
            container.innerHTML = '';
            
            if (data.notifications.length === 0) {
                container.innerHTML = '<div class="dropdown-item text-center">No notifications</div>';
                return;
            }
            
            // Add notifications
            data.notifications.forEach(notification => {
                let icon;
                
                // Determine icon based on status
                switch(notification.status) {
                    case 'pending':
                        icon = 'fas fa-clock text-secondary';
                        break;
                    case 'approved':
                        icon = 'fas fa-check text-success';
                        break;
                    case 'rejected':
                        icon = 'fas fa-times text-danger';
                        break;
                    case 'packed':
                        icon = 'fas fa-box text-primary';
                        break;
                    case 'shipped':
                        icon = 'fas fa-truck text-info';
                        break;
                    case 'delivered':
                        icon = 'fas fa-check-circle text-success';
                        break;
                    case 'cancelled':
                        icon = 'fas fa-ban text-danger';
                        break;
                    default:
                        icon = 'fas fa-bell text-primary';
                }
                
                // Create notification item
                const item = document.createElement('a');
                item.href = `/notifications/mark-read/${notification.id}`;
                item.className = `dropdown-item notification-item ${notification.is_read ? '' : 'unread'}`;

                const createdAt = new Date(notification.created_at);
                const now = new Date();
                const diff = Math.floor((now - createdAt) / 1000); // Difference in seconds

                let timeAgo;
                if (diff < 60) {
                    timeAgo = `${diff} seconds ago`;
                } else if (diff < 3600) {
                    timeAgo = `${Math.floor(diff / 60)} minutes ago`;
                } else if (diff < 86400) {
                    timeAgo = `${Math.floor(diff / 3600)} hours ago`;
                } else {
                    timeAgo = `${Math.floor(diff / 86400)} days ago`;
                }

                const orderId = notification.order ? notification.order.id : 'Unknown';
                // Format status for display
                let statusText;
                switch(notification.status) {
                    case 'pending':
                        statusText = 'Pending Approval';
                        break;
                    case 'approved':
                        statusText = 'Approved';
                        break;
                    case 'rejected':
                        statusText = 'Rejected';
                        break;
                    case 'packed':
                        statusText = 'Packed';
                        break;
                    case 'shipped':
                        statusText = 'Shipped';
                        break;
                    case 'delivered':
                        statusText = 'Delivered';
                        break;
                    case 'cancelled':
                        statusText = 'Cancelled';
                        break;
                    default:
                        statusText = notification.status.charAt(0).toUpperCase() + notification.status.slice(1);
                }

                item.innerHTML = `
                    <div class="d-flex">
                        <div class="notification-icon me-3">
                            <i class="${icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-text">Order #${orderId} - ${statusText}</div>
                            <div class="notification-time">${timeAgo}</div>
                        </div>
                    </div>
                `;
                
                container.appendChild(item);
            });
            
            // Update unread count
            const badge = document.querySelector('.notification-badge');
            if (data.unreadCount > 0) {
                if (badge) {
                    badge.textContent = data.unreadCount;
                    badge.style.display = 'block';
                }
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(error => console.error('Error loading notifications:', error));
}

// Initialize notification system
document.addEventListener('DOMContentLoaded', function() {
    // Load initial notifications
    updateNotificationCount();
    
    // Setup notification dropdown
    const notificationDropdown = document.getElementById('notificationDropdown');
    if (notificationDropdown) {
        notificationDropdown.addEventListener('click', function() {
            loadRecentNotifications();
        });
    }
    
    // Refresh notifications every 60 seconds
    setInterval(updateNotificationCount, 60000);
});