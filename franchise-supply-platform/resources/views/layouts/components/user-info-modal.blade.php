{{-- 
  User Info Modal Component
  This component displays detailed user information in a modal
  Place this file at: resources/views/layouts/components/user-info-modal.blade.php
--}}

<style>
/* Modal styling */
.user-info-modal .modal-header {
    background-color: #4e73df;
    color: white;
    border-bottom: 1px solid #3a5fc7;
}

.user-info-modal .modal-footer {
    background-color: #f8f9fc;
    border-top: 1px solid #e3e6f0;
}

.user-info-card {
    border-radius: 0.35rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    margin-bottom: 1.5rem;
}

.user-info-card .card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    font-weight: 700;
    color: #4e73df;
}

.user-info-label {
    font-weight: 600;
    color: #5a5c69;
}

.user-badge {
    margin-left: 10px;
}

.user-info-section {
    margin-bottom: 1.5rem;
}

.detail-row {
    padding: 0.75rem;
    border-bottom: 1px solid #e3e6f0;
}

.detail-row:last-child {
    border-bottom: none;
}

.text-muted-light {
    color: #b7b9cc;
}

/* Company logo */
.company-logo {
    max-width: 100%;
    max-height: 100px;
    display: block;
    margin: 0 auto;
    border-radius: 5px;
}

.company-logo-placeholder {
    width: 100%;
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fc;
    border: 1px dashed #d1d3e2;
    border-radius: 5px;
    color: #858796;
}
</style>

{{-- User Info Modal --}}
<div class="modal fade user-info-modal" id="userInfoModal" tabindex="-1" aria-labelledby="userInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userInfoModalLabel">
                    <i class="fas fa-user-circle me-2"></i> User Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user information...</p>
                </div>
                
                <div id="userInfoContent" class="d-none">
                    <!-- User Profile Header - Now Full Width -->
                    <div class="mb-4">
                        <!-- Basic Info Card -->
                        <div class="card user-info-card">
                            <div class="card-header py-3 d-flex align-items-center">
                                <h6 class="m-0 font-weight-bold">Account Information</h6>
                                <div class="ms-auto d-flex align-items-center">
                                    <span id="userStatus" class="me-2"><span class="badge bg-success">Active</span></span>
                                    <span id="userRole"><span class="badge bg-primary">Role</span></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">User ID</div>
                                    <div class="col-md-9" id="userId">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Username</div>
                                    <div class="col-md-9" id="userUsername">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Email</div>
                                    <div class="col-md-9" id="userEmail">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Phone</div>
                                    <div class="col-md-9" id="userPhone">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Status</div>
                                    <div class="col-md-9" id="userStatusText">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Created At</div>
                                    <div class="col-md-9" id="userCreatedAt">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Last Updated</div>
                                    <div class="col-md-9" id="userUpdatedAt">-</div>
                                </div>
                                <div class="row detail-row">
                                    <div class="col-md-3 user-info-label">Updated By</div>
                                    <div class="col-md-9" id="userUpdatedBy">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Profile Details -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card user-info-card">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold">Profile Details</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Company Info -->
                                    <div id="companySection">
                                        <h6 class="mb-3 border-bottom pb-2">Company Information</h6>
                                        <div class="row mb-3">
                                            <div class="col-md-3 text-center mb-3">
                                              <div id="companyLogoContainer">
                                                <div class="company-logo-placeholder" id="defaultLogoPlaceholder">
                                                    <i class="fas fa-building fa-2x"></i>
                                                </div>
                                                <img id="companyLogoImage" src="" class="company-logo d-none" alt="Company Logo">
                                              </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row detail-row">
                                                    <div class="col-md-4 user-info-label">Company Name</div>
                                                    <div class="col-md-8" id="companyName">-</div>
                                                </div>
                                                <div class="row detail-row">
                                                    <div class="col-md-4 user-info-label">Contact Name</div>
                                                    <div class="col-md-8" id="contactName">-</div>
                                                </div>
                                                <div class="row detail-row">
                                                    <div class="col-md-4 user-info-label">Website</div>
                                                    <div class="col-md-8" id="companyWebsite">-</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Address Info -->
                                    <div id="addressSection">
                                        <h6 class="mb-3 border-bottom pb-2">Address</h6>
                                        <div class="row detail-row">
                                            <div class="col-md-4 user-info-label">Street</div>
                                            <div class="col-md-8" id="userAddress">-</div>
                                        </div>
                                        <div class="row detail-row">
                                            <div class="col-md-4 user-info-label">City</div>
                                            <div class="col-md-8" id="userCity">-</div>
                                        </div>
                                        <div class="row detail-row">
                                            <div class="col-md-4 user-info-label">State</div>
                                            <div class="col-md-8" id="userState">-</div>
                                        </div>
                                        <div class="row detail-row">
                                            <div class="col-md-4 user-info-label">Postal Code</div>
                                            <div class="col-md-8" id="userPostalCode">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="userOrdersBtn" onclick="closeModalAndNavigate(event)">
                    <i class="fas fa-shopping-cart me-1"></i> View Orders
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup the user info modal functionality
    setupUserInfoModal();
});

function setupUserInfoModal() {
    // Get all view user buttons
    const viewUserButtons = document.querySelectorAll('.view-user-btn');
    const modal = document.getElementById('userInfoModal');
    
    if (!modal) return;
    
    // Create a Bootstrap modal instance
    const modalInstance = new bootstrap.Modal(modal);
    
    // Add event listeners to all view buttons
    viewUserButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the user ID from the button's data attribute
            const userId = this.dataset.userId;
            if (!userId) return;
            
            // Show modal with loading state
            modalInstance.show();
            
            // Hide content and show loading spinner
            const userInfoContent = document.getElementById('userInfoContent');
            if (userInfoContent) userInfoContent.classList.add('d-none');
            
            const loadingSpinner = modal.querySelector('.spinner-border')?.parentNode;
            if (loadingSpinner) loadingSpinner.classList.remove('d-none');
            
            // Get current CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Updated URL to match the correct route
            fetch(`/admin/users/${userId}/info`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    // Convert non-2xx HTTP responses into errors
                    return response.json().then(data => {
                        throw new Error(data.message || `Server returned ${response.status}: ${response.statusText}`);
                    }).catch(error => {
                        // This catch handles the case where the response is not valid JSON
                        throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                // Hide loading spinner and show content
                if (loadingSpinner) loadingSpinner.classList.add('d-none');
                if (userInfoContent) userInfoContent.classList.remove('d-none');
                
                // Check if data is valid
                if (!data || !data.user) {
                    throw new Error('Invalid data received from server');
                }
                
                // Log the data for debugging
                console.log('User data received:', data);
                
                // Populate the modal with user data
                populateUserModal(data);
                
                // Update the orders button with the user ID
                const ordersBtn = document.getElementById('userOrdersBtn');
                if (ordersBtn) {
                    // Store the user ID as a data attribute instead of directly setting the href
                    ordersBtn.dataset.userId = userId;
                }
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                
                // Hide loading spinner and show error message
                if (loadingSpinner) loadingSpinner.classList.add('d-none');
                
                const errorContent = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error loading user information:</strong><br>
                        ${error.message || 'Unknown error occurred. Please try again.'}
                    </div>
                    <div class="text-center mt-3 mb-3">
                        <button type="button" class="btn btn-primary" onclick="retryLoadUser(${userId})">
                            <i class="fas fa-redo me-2"></i> Try Again
                        </button>
                    </div>
                `;
                
                if (userInfoContent) {
                    userInfoContent.innerHTML = errorContent;
                    userInfoContent.classList.remove('d-none');
                }
            });
        });
    });
}

// Function to close modal and then navigate to orders page
function closeModalAndNavigate(event) {
    event.preventDefault();
    
    // Get the user ID from the button's data attribute
    const userId = document.getElementById('userOrdersBtn').dataset.userId;
    
    // Get the modal instance
    const modal = document.getElementById('userInfoModal');
    const modalInstance = bootstrap.Modal.getInstance(modal);
    
    // Define where to navigate after modal is hidden
    const navigateToOrders = function() {
        window.location.href = `/admin/orders?user_id=${userId}`;
    };
    
    // Add one-time event listener for when modal is hidden
    modal.addEventListener('hidden.bs.modal', function handler() {
        // Remove the event listener to prevent memory leaks
        modal.removeEventListener('hidden.bs.modal', handler);
        // Navigate to orders page
        navigateToOrders();
    });
    
    // Hide the modal
    modalInstance.hide();
}

// Function to retry loading user data
function retryLoadUser(userId) {
    const modal = document.getElementById('userInfoModal');
    if (!modal) return;
    
    // Hide content and show loading spinner
    const userInfoContent = document.getElementById('userInfoContent');
    if (userInfoContent) userInfoContent.classList.add('d-none');
    
    const loadingSpinner = modal.querySelector('.spinner-border')?.parentNode;
    if (loadingSpinner) loadingSpinner.classList.remove('d-none');
    
    // Get a view button with this user ID
    const viewBtn = document.querySelector(`.view-user-btn[data-user-id="${userId}"]`);
    if (viewBtn) {
        // Simulate a click on the view button
        viewBtn.click();
    }
}

function populateUserModal(data) {
    try {
        // Basic user information - Add null checks for all DOM elements
        const userEmailElement = document.getElementById('userEmail');
        const userIdElement = document.getElementById('userId');
        const userUsernameElement = document.getElementById('userUsername');
        const userPhoneElement = document.getElementById('userPhone');
        const userCreatedAtElement = document.getElementById('userCreatedAt');
        const userUpdatedAtElement = document.getElementById('userUpdatedAt');
        const userUpdatedByElement = document.getElementById('userUpdatedBy');
        const userRoleElement = document.getElementById('userRole');
        const userStatusElement = document.getElementById('userStatus');
        const userStatusTextElement = document.getElementById('userStatusText');
        
        // Safe updates with null checks
        if (userEmailElement) userEmailElement.textContent = data.user.email || 'N/A';
        if (userIdElement) userIdElement.textContent = data.user.id || 'N/A';
        if (userUsernameElement) userUsernameElement.textContent = data.user.username || 'N/A';
        if (userPhoneElement) userPhoneElement.textContent = data.user.phone || 'Not provided';
        if (userCreatedAtElement) userCreatedAtElement.textContent = formatDate(data.user.created_at);
        if (userUpdatedAtElement) userUpdatedAtElement.textContent = formatDate(data.user.updated_at);
        if (userUpdatedByElement) userUpdatedByElement.textContent = data.user.updated_by || 'N/A';
        
        // Set status badge and text
        const isActive = !!data.user.status;
        if (userStatusElement) {
            userStatusElement.innerHTML = getStatusBadge(isActive);
        }
        if (userStatusTextElement) {
            userStatusTextElement.textContent = isActive ? 'Active' : 'Blocked';
        }
        
        // Set role badge
        if (userRoleElement) {
            userRoleElement.innerHTML = getRoleBadge(data.user.role);
        }
        
        // Company and profile information - These will handle their own null checks
        updateProfileSection(data);
    } catch (error) {
        console.error('Error populating user modal:', error);
        
        // Find the content element to show the error
        const contentElement = document.getElementById('userInfoContent');
        if (contentElement) {
            contentElement.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error displaying user data:</strong><br>
                    ${error.message || 'Unknown error occurred while displaying user information.'}
                </div>
            `;
        }
    }
}

function updateProfileSection(data) {
    try {
        const hasProfile = data.profile !== null;
        
        // Get all the DOM elements with null checks
        const elements = {
            'companyName': document.getElementById('companyName'),
            'contactName': document.getElementById('contactName'),
            'companyWebsite': document.getElementById('companyWebsite'),
            'userAddress': document.getElementById('userAddress'),
            'userCity': document.getElementById('userCity'),
            'userState': document.getElementById('userState'),
            'userPostalCode': document.getElementById('userPostalCode')
        };
        
        // Prepare the values
        const values = {
            'companyName': hasProfile && data.profile?.company_name ? data.profile.company_name : 'Not provided',
            'contactName': hasProfile && data.profile?.contact_name ? data.profile.contact_name : 'Not provided',
            'companyWebsite': hasProfile && data.profile?.website ? data.profile.website : 'Not provided',
            'userAddress': hasProfile && data.profile?.address ? data.profile.address : 'Not provided',
            'userCity': hasProfile && data.profile?.city ? data.profile.city : 'Not provided',
            'userState': hasProfile && data.profile?.state ? data.profile.state : 'Not provided',
            'userPostalCode': hasProfile && data.profile?.postal_code ? data.profile.postal_code : 'Not provided'
        };
        
        // Update each field safely
        Object.entries(elements).forEach(([id, element]) => {
            if (element) {
                element.textContent = values[id];
            }
        });
        
        // Set company logo if available
        const logoContainer = document.getElementById('companyLogoContainer');
        const logoImage = document.getElementById('companyLogoImage');
        const defaultLogo = document.getElementById('defaultLogoPlaceholder');
        
        if (logoContainer && logoImage && defaultLogo) {
            if (hasProfile && data.profile?.logo_path) {
                logoImage.src = `/storage/${data.profile.logo_path}`;
                logoImage.classList.remove('d-none');
                defaultLogo.classList.add('d-none');
            } else {
                logoImage.classList.add('d-none');
                defaultLogo.classList.remove('d-none');
            }
        }
    } catch (error) {
        console.error('Error updating profile section:', error);
        // Continue execution - don't throw error to prevent stopping other updates
    }
}

// Helper function to format date - with better error handling
function formatDate(dateString) {
    try {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return 'Invalid Date';
        }
        
        return new Intl.DateTimeFormat('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    } catch (e) {
        console.error('Error formatting date:', e);
        return dateString || 'N/A';
    }
}

// Helper function to format short date - for last order date display
function formatDateShort(dateString) {
    try {
        if (!dateString) return '-';
        
        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            return '-';
        }
        
        return new Intl.DateTimeFormat('en-US', { 
            month: 'short', 
            day: 'numeric'
        }).format(date);
    } catch (e) {
        console.error('Error formatting short date:', e);
        return '-';
    }
}

// Helper function to generate role badge HTML - with better error handling
function getRoleBadge(role) {
    try {
        if (!role) return '<span class="badge bg-secondary">Unknown</span>';
        
        const roleName = role.name || 'Unknown';
        let badgeClass = 'bg-secondary';
        
        switch (String(roleName).toLowerCase()) {
            case 'admin':
                badgeClass = 'bg-danger';
                break;
            case 'franchisee':
                badgeClass = 'bg-success';
                break;
            case 'warehouse':
                badgeClass = 'bg-primary';
                break;
        }
        
        return `<span class="badge ${badgeClass}">${String(roleName).charAt(0).toUpperCase() + String(roleName).slice(1)}</span>`;
    } catch (error) {
        console.error('Error creating role badge:', error);
        return '<span class="badge bg-secondary">Unknown</span>';
    }
}

// Helper function to generate status badge HTML
function getStatusBadge(isActive) {
    try {
        if (isActive) {
            return '<span class="badge bg-success">Active</span>';
        } else {
            return '<span class="badge bg-danger">Blocked</span>';
        }
    } catch (error) {
        console.error('Error creating status badge:', error);
        return '<span class="badge bg-secondary">Unknown</span>';
    }
}
</script>