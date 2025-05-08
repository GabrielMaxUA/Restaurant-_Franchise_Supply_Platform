@extends('layouts.admin')

@section('title', 'My Profile - Admin Portal')

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

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-user-circle me-2"></i>
            <span>Basic Information</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="role" class="form-label">Role</label>
                        <input type="text" class="form-control bg-light" id="role" value="{{ $user->role ? ucfirst($user->role->name) : 'Admin' }}" readonly>
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
    
    <div class="card">
        <div class="card-header d-flex align-items-center">
            <i class="fas fa-shield-alt me-2"></i>
            <span>Security Settings</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>Password</h6>
                    <p class="text-muted small mb-3">Secure your account with a strong password</p>
                    <a href="{{ route('admin.profile.settings', ['tab' => 'password']) }}" class="btn btn-outline-primary btn-sm">
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