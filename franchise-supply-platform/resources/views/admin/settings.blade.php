@extends('layouts.admin')

@section('title', 'Account Settings - Admin Portal')

@section('page-title', 'Account Settings')

@section('styles')
<style>
    .container {
        max-width: 85%;
        margin: 0 auto;
    }
    
    .card {
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
        border: none;
    }
    
    .welcome-alert {
        background-color: #e8f4f8;
        border-left: 4px solid #2c7be5;
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-radius: 4px;
    }
    
    .welcome-icon {
        color: #2c7be5;
        margin-right: 0.5rem;
    }
    
    .nav-tabs {
        border-bottom: 1px solid #e9ecef;
    }
    
    .nav-tabs .nav-link {
        border: none;
        color: #495057;
        padding: 0.75rem 1.25rem;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link.active {
        color: #2c7be5;
        background-color: transparent;
        border-bottom: 2px solid #2c7be5;
    }
    
    .nav-link-icon {
        margin-right: 0.5rem;
        color: #6c757d;
    }
    
    .nav-link.active .nav-link-icon {
        color: #2c7be5;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.3rem;
    }
    
    .password-meter {
        height: 4px;
        background-color: #e9ecef;
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
        border-radius: 2px;
    }
    
    .password-meter-progress {
        height: 100%;
        border-radius: 2px;
        transition: width 0.3s ease;
    }
    
    .password-meter-text {
        font-size: 0.75rem;
        color: #6c757d;
    }
    
    .form-control {
        padding: 0.5rem 0.75rem;
    }
    
    .password-input-group {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
    }

    .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .back-link {
        display: inline-flex;
        align-items: center;
        color: #6c757d;
        text-decoration: none;
        transition: color 0.2s;
    }
    
    .back-link:hover {
        color: #343a40;
    }
    
    .back-link i {
        margin-right: 0.5rem;
    }
    
    /* Center the password form */
    .centered-form {
        max-width: 50%;
        margin: 0 auto;
    }
    
    /* Password validation styles */
    .validation-status {
        display: flex;
        align-items: center;
        margin-top: 0.25rem;
    }
    
    .validation-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 0.5rem;
        font-size: 0.75rem;
        color: white;
    }
    
    .valid-icon {
        background-color: #198754;
    }
    
    .invalid-icon {
        background-color: #dc3545;
    }
    
    .validation-message {
        font-size: 0.75rem;
    }
    
    .matches-message {
        color: #198754;
    }
    
    .not-matches-message {
        color: #dc3545;
    }
    
    /* Hide validation messages by default */
    .validation-status {
        display: none;
    }
    
    /* Input borders for password matching */
    .password-match {
        border-color: #198754;
    }
    
    .password-mismatch {
        border-color: #dc3545;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="action-bar">
        <a href="{{ route('admin.profile.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="welcome-alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-star welcome-icon fs-5 mt-1"></i>
            <div>
                <h5 class="mb-1">Welcome back, {{ $user->username }}!</h5>
                <p class="mb-0">Check the dashboard for more insights about the supply platform status.</p>
            </div>
        </div>
    </div>
    
    <ul class="nav nav-tabs" id="settings-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab != 'password' ? 'active' : '' }}" id="company-tab" data-bs-toggle="tab" href="#company-tab-pane" role="tab" aria-controls="company-tab-pane" aria-selected="{{ $tab != 'password' ? 'true' : 'false' }}">
                <i class="fas fa-building nav-link-icon"></i> Company Information
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a class="nav-link {{ $tab == 'password' ? 'active' : '' }}" id="password-tab" data-bs-toggle="tab" href="#password-tab-pane" role="tab" aria-controls="password-tab-pane" aria-selected="{{ $tab == 'password' ? 'true' : 'false' }}">
                <i class="fas fa-key nav-link-icon"></i> Change Password
            </a>
        </li>
    </ul>
    
    <div class="tab-content p-3 border border-top-0 rounded-bottom" id="settings-tab-content">
        <!-- Company Information Tab -->
        <div class="tab-pane fade {{ $tab != 'password' ? 'show active' : '' }}" id="company-tab-pane" role="tabpanel" aria-labelledby="company-tab">
    <div class="mb-4">
        <h5 class="mb-3">Company Information</h5>
        <p class="text-muted small">Update your company's details for invoices and receipts</p>
    </div>
    
    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row mb-4">
            <div class="col-md-6">
                <!-- Company Logo -->
                <label for="logo" class="form-label">Company Logo</label>
                <div class="mb-3 d-flex align-items-center">
                    @if($adminDetail && $adminDetail->logo_path)
                        <div class="me-3">
                            <img src="{{ asset('storage/' . $adminDetail->logo_path) }}" alt="Company Logo" class="img-thumbnail" style="max-height: 100px;">
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                        <div class="form-text">Recommended size: 300x100px. Max size: 2MB.</div>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <!-- Company Name -->
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $adminDetail->company_name ?? 'Restaurant Franchise Supply') }}">
                @error('company_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $adminDetail->address ?? '123 Main Street') }}">
                @error('address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="city" class="form-label">City</label>
                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $adminDetail->city ?? 'Toronto') }}">
                @error('city')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4">
                <label for="state" class="form-label">State/Province</label>
                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $adminDetail->state ?? 'ON') }}">
                @error('state')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-4">
                <label for="postal_code" class="form-label">Postal Code</label>
                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $adminDetail->postal_code ?? 'M4J 2G5') }}">
                @error('postal_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $adminDetail->phone ?? $user->phone ?? '') }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6">
                <label for="website" class="form-label">Website</label>
                <input type="text" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', $adminDetail->website ?? 'www.restaurantfranchisesupply.com') }}">
                @error('website')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i> Save Changes
            </button>
        </div>
    </form>
</div>
        
        <!-- Password Settings Tab -->
        <div class="tab-pane fade {{ $tab == 'password' ? 'show active' : '' }}" id="password-tab-pane" role="tabpanel" aria-labelledby="password-tab">
            <div class="text-center mb-4">
                <h5 class="mb-1">Change Password</h5>
                <p class="text-muted small">Update your password to keep your account secure</p>
            </div>
            
            <form class="centered-form" action="{{ route('admin.profile.change-password') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="current_password" class="form-label">Current Password</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                        <button type="button" class="password-toggle" data-target="current_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('current_password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control @error('new_password') is-invalid @enderror" id="new_password" name="new_password" required>
                        <button type="button" class="password-toggle" data-target="new_password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('new_password')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    
                    <div class="password-meter">
                        <div id="password-strength-meter" class="password-meter-progress bg-danger" style="width: 0%"></div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small id="password-strength-text" class="password-meter-text">Too weak</small>
                        <small class="password-meter-text">Min. 8 characters</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                    <div class="password-input-group">
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                        <button type="button" class="password-toggle" data-target="new_password_confirmation">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    
                    <!-- Password match validation message (hidden by default) -->
                    <div id="passwords-match" class="validation-status">
                        <div class="validation-icon valid-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="validation-message matches-message">Passwords match</span>
                    </div>
                    
                    <!-- Password mismatch validation message (hidden by default) -->
                    <div id="passwords-mismatch" class="validation-status">
                        <div class="validation-icon invalid-icon">
                            <i class="fas fa-times"></i>
                        </div>
                        <span class="validation-message not-matches-message">Passwords do not match</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="alert alert-info py-2 px-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong class="small">Password Requirements</strong>
                        </div>
                        <ul class="mb-0 small ps-4 mt-1">
                            <li>Minimum 8 characters</li>
                            <li>At least one uppercase letter (A-Z)</li>
                            <li>At least one lowercase letter (a-z)</li>
                            <li>At least one number (0-9)</li>
                            <li>At least one special character (!@#$%^&*)</li>
                        </ul>
                    </div>
                </div>
                
                <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key me-2"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const toggleButtons = document.querySelectorAll('.password-toggle');
        
        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        
        // Password strength meter
        const passwordInput = document.getElementById('new_password');
        const strengthMeter = document.getElementById('password-strength-meter');
        const strengthText = document.getElementById('password-strength-text');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                
                // Check length (max 20%)
                if (password.length >= 8) {
                    strength += 20;
                } else if (password.length >= 5) {
                    strength += 10;
                }
                
                // Check lowercase letters (max 20%)
                if (password.match(/[a-z]+/)) {
                    strength += 20;
                }
                
                // Check uppercase letters (max 20%)
                if (password.match(/[A-Z]+/)) {
                    strength += 20;
                }
                
                // Check numbers (max 20%)
                if (password.match(/[0-9]+/)) {
                    strength += 20;
                }
                
                // Check special characters (max 20%)
                if (password.match(/[^a-zA-Z0-9]+/)) {
                    strength += 20;
                }
                
                // Update the strength meter
                strengthMeter.style.width = strength + '%';
                
                // Update the color and text
                if (strength < 40) {
                    strengthMeter.className = 'password-meter-progress bg-danger';
                    strengthText.textContent = 'Too weak';
                } else if (strength < 70) {
                    strengthMeter.className = 'password-meter-progress bg-warning';
                    strengthText.textContent = 'Medium';
                } else {
                    strengthMeter.className = 'password-meter-progress bg-success';
                    strengthText.textContent = 'Strong';
                }
                
                // Special case for empty password
                if (password.length === 0) {
                    strengthMeter.style.width = '0%';
                    strengthText.textContent = 'Too weak';
                }
                
                // Check if confirmation field matches whenever password changes
                checkPasswordMatch();
            });
            
            // Check if passwords match
            const confirmPassword = document.getElementById('new_password_confirmation');
            const matchMessage = document.getElementById('passwords-match');
            const mismatchMessage = document.getElementById('passwords-mismatch');
            
            function checkPasswordMatch() {
                const passwordValue = passwordInput.value;
                const confirmValue = confirmPassword.value;
                
                if (confirmValue) {
                    if (passwordValue === confirmValue) {
                        // Show match message
                        matchMessage.style.display = 'flex';
                        mismatchMessage.style.display = 'none';
                        confirmPassword.classList.add('password-match');
                        confirmPassword.classList.remove('password-mismatch');
                    } else {
                        // Show mismatch message
                        mismatchMessage.style.display = 'flex';
                        matchMessage.style.display = 'none';
                        confirmPassword.classList.add('password-mismatch');
                        confirmPassword.classList.remove('password-match');
                    }
                } else {
                    // Hide both messages when confirmation is empty
                    matchMessage.style.display = 'none';
                    mismatchMessage.style.display = 'none';
                    confirmPassword.classList.remove('password-match', 'password-mismatch');
                }
            }
            
            if (confirmPassword) {
                confirmPassword.addEventListener('input', checkPasswordMatch);
            }
        }
        
        // Set active tab from URL hash if present
        const hash = window.location.hash;
        if (hash && hash === '#password') {
            const passwordTab = document.getElementById('password-tab');
            if (passwordTab) {
                passwordTab.click();
            }
        }
    });
</script>
@endsection