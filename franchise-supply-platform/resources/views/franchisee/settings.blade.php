@extends('layouts.franchisee')

@section('title', 'Account Settings - Franchisee Portal')

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
        <a href="{{ route('franchisee.profile') }}" class="back-link">
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
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-key me-2"></i> Change Password</h5>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <p class="text-muted small">Update your password to keep your account secure</p>
            </div>
            
            <form class="centered-form" action="{{ route('franchisee.profile.update-password') }}" method="POST">
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
    });
</script>
@endsection