@extends('layouts.franchisee')

@section('title', 'My Profile - Franchisee Portal')

@section('page-title', 'My Profile')

@section('styles')
<style>
    .container {
        max-width: 80%;
        margin: 0 auto;
    }
    
    .card {
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
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
    
    .last-updated {
        font-size: 0.8rem;
        color: #6c757d;
        display: flex;
        align-items: center;
    }
    
    .last-updated i {
        margin-right: 0.25rem;
    }
    
    .btn-primary {
        background-color: #2c7be5;
        border-color: #2c7be5;
    }
    
    .btn-outline-primary {
        color: #2c7be5;
        border-color: #2c7be5;
    }
    
    .btn-outline-primary:hover {
        background-color: #2c7be5;
        color: white;
    }

    /* Basic information card style */
    .section-card {
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12);
        margin-bottom: 1.5rem;
    }

    .section-header {
        display: flex;
        align-items: center;
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #dee2e6;
        font-weight: 500;
    }

    .section-header i {
        margin-right: 0.5rem;
        color: #495057;
    }

    .section-body {
        padding: 1.5rem;
    }

    .form-label {
        font-weight: 500;
        color: #495057;
    }

    .form-control {
        border-color: #dee2e6;
    }

    .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
</style>
@endsection

@section('content')
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

    <!-- Basic Information -->
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-user-circle"></i>
            <span>Basic Information</span>
        </div>
        <div class="section-body">
            <form action="{{ route('franchisee.profile.update-basic') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="{{ old('username', $user->username) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $user->email) }}">
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="contact_name" class="form-label">Contact Person Name</label>
                        <input type="text" class="form-control" id="contact_name" name="contact_name" value="{{ old('contact_name', $profile->contact_name ?? '') }}">
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="last-updated">
                        <i class="fas fa-clock"></i> Last updated: {{ $user->updated_at ? $user->updated_at->format('M d, Y, h:i A') : 'Never' }}
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="section-card">
        <div class="section-header">
            <i class="fas fa-shield-alt"></i>
            <span>Security Settings</span>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Password</h6>
                    <p class="text-muted small mb-3">Secure your account with a strong password</p>
                    <a href="{{ route('franchisee.settings') }}?tab=password" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-key me-1"></i> Change Password
                    </a>
                </div>
                <div class="col-md-6">
                    <h6>Last Login</h6>
                    <p class="text-muted small">{{ now()->format('M d, Y, h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection