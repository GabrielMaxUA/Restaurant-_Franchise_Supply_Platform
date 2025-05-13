@extends('layouts.admin')

@section('title', 'Account Settings - Admin Portal')

@section('page-title', 'Account Settings')

@section('styles')
    <style>
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: none;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem;
            font-weight: 500;
        }
        .card-body {
            padding: 1.5rem;
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
        .nav-tabs .nav-link {
            border: none;
            color: #495057;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border-radius: 0;
        }
        .nav-tabs .nav-link.active {
            color: #4e73df;
            background: none;
            border-bottom: 2px solid #4e73df;
        }
        .tab-content {
            padding: 1.5rem 0;
        }
    </style>
@endsection

@section('content')
{{-- For debugging purposes, show current state of variables --}}
@if(config('app.debug'))
    <div class="container mb-3">
        <div class="alert alert-info">
            <h6>Debug Information:</h6>
            <ul class="mb-0">
                <li>User ID: {{ $user->id }}</li>
                <li>Username: {{ $user->username }}</li>
                <li>Admin Detail: {{ isset($adminDetail) ? 'Yes (ID: '.$adminDetail->id.')' : 'No' }}</li>
                @if(isset($adminDetail))
                    <li>Company Name: {{ $adminDetail->company_name ?? 'Not set' }}</li>
                    <li>Address: {{ $adminDetail->address ?? 'Not set' }}</li>
                @endif
                <li>Company Config: {{ config('company.name') }}</li>
            </ul>
        </div>
    </div>
@endif

<div class="container">
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
    
    <div class="mb-3">
        <a href="{{ route('admin.profile.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>
    
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-cog me-2"></i>
            <span>Account Settings</span>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab != 'password' ? 'active' : '' }}" id="profile-tab" data-bs-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="{{ $tab != 'password' ? 'true' : 'false' }}">
                        <i class="fas fa-user me-2"></i> Profile Information
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab == 'password' ? 'active' : '' }}" id="password-tab" data-bs-toggle="tab" href="#password" role="tab" aria-controls="password" aria-selected="{{ $tab == 'password' ? 'true' : 'false' }}">
                        <i class="fas fa-key me-2"></i> Change Password
                    </a>
                </li>
            </ul>
            
            <div class="tab-content" id="settingsTabsContent">
                <!-- Profile Information Tab -->
                <div class="tab-pane fade {{ $tab != 'password' ? 'show active' : '' }}" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}" required>
                                @error('username')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', isset($adminDetail) ? $adminDetail->company_name : config('company.name')) }}">
                                @error('company_name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', isset($adminDetail) ? $adminDetail->address : config('company.address')) }}">
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', isset($adminDetail) ? $adminDetail->city : config('company.city')) }}">
                                @error('city')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State/Province</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', isset($adminDetail) ? $adminDetail->state : config('company.state')) }}">
                                @error('state')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', isset($adminDetail) ? $adminDetail->postal_code : config('company.zip')) }}">
                                @error('postal_code')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="text" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', isset($adminDetail) ? $adminDetail->website : config('company.website')) }}">
                            @error('website')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="logo" class="form-label">Company Logo</label>
                            @if($adminDetail && $adminDetail->logo_path)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $adminDetail->logo_path) }}" alt="Company Logo" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo">
                            <div class="form-text">Recommended size: 240x80 pixels. Max file size: 2MB. Supported formats: JPG, PNG, GIF.</div>
                            @error('logo')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Tab -->
                <div class="tab-pane fade {{ $tab == 'password' ? 'show active' : '' }}" id="password" role="tabpanel" aria-labelledby="password-tab">
                    <form action="{{ route('admin.profile.change-password') }}" method="POST">
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
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                <button type="button" class="password-toggle" data-target="new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tabs from URL parameter if present
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');
        if (tab === 'password') {
            const passwordTab = document.getElementById('password-tab');
            if (passwordTab) {
                new bootstrap.Tab(passwordTab).show();
            }
        }
        
        // Password toggle functionality
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
    });
</script>
@endsection