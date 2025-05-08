@extends('layouts.franchisee')

@section('title', 'My Profile - Franchisee Portal')

@section('page-title', 'My Profile')

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
    
    .section-divider {
        border-top: 1px solid rgba(0,0,0,.1);
        margin: 1.5rem 0;
        position: relative;
    }
    
    .section-divider span {
        position: absolute;
        top: -12px;
        background: white;
        padding: 0 10px;
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
        left: 20px;
    }
    
    .company-logo-container {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
        background-color: #f8f9fa;
    }
    
    .company-logo-preview {
        max-width: 100%;
        max-height: 150px;
        margin-bottom: 15px;
    }
    
    .logo-actions {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
    }
    
    .logo-placeholder {
        width: 150px;
        height: 150px;
        border-radius: 8px;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        color: #6c757d;
    }
    
    .logo-placeholder i {
        font-size: 3rem;
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

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <form action="{{ route('franchisee.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><i class="fas fa-user-circle me-2"></i> Franchisee Profile</div>
                <a href="{{ route('franchisee.settings') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-key me-1"></i> Change Password
                </a>
            </div>
            <div class="card-body">
                <!-- Basic Information Section -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" name="username" value="{{ old('username', $user->username) }}">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="contact_name" class="form-label">Contact Person Name</label>
                        <input type="text" class="form-control @error('contact_name') is-invalid @enderror" id="contact_name" name="contact_name" value="{{ old('contact_name', $profile->contact_name ?? '') }}">
                        @error('contact_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <!-- Company Information Section -->
                <div class="section-divider">
                    <span>Company Information</span>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="company_name" class="form-label">Company Name</label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name', $profile->company_name ?? '') }}">
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <!-- Company Logo Upload -->
                        <label class="form-label">Company Logo</label>
                        <div class="company-logo-container">
                            @if(isset($profile) && $profile->logo_path)
                                <img src="{{ asset('storage/' . $profile->logo_path) }}" alt="Company Logo" class="company-logo-preview" id="logoPreview">
                                <div class="logo-actions">
                                    <div class="custom-file">
                                        <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                                        @error('logo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo">
                                        <label class="form-check-label" for="remove_logo">
                                            Remove logo
                                        </label>
                                    </div>
                                </div>
                            @else
                                <div class="logo-placeholder" id="logoPlaceholder">
                                    <i class="fas fa-building"></i>
                                </div>
                                <img src="" alt="Company Logo" class="company-logo-preview d-none" id="logoPreview">
                                <div class="custom-file">
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*" onchange="previewLogo(this)">
                                    <small class="form-text text-muted">Upload your company logo (JPG, PNG, GIF). Max size: 2MB</small>
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" id="address" name="address" value="{{ old('address', $profile->address ?? '') }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control @error('city') is-invalid @enderror" id="city" name="city" value="{{ old('city', $profile->city ?? '') }}">
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="state" class="form-label">State/Province</label>
                        <input type="text" class="form-control @error('state') is-invalid @enderror" id="state" name="state" value="{{ old('state', $profile->state ?? '') }}">
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="postal_code" class="form-label">Postal Code</label>
                        <input type="text" class="form-control @error('postal_code') is-invalid @enderror" id="postal_code" name="postal_code" value="{{ old('postal_code', $profile->postal_code ?? '') }}">
                        @error('postal_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <div class="last-updated">
                        <i class="fas fa-clock"></i> Last updated: {{ $user->updated_at ? $user->updated_at->format('M d, Y, h:i A') : 'Never' }}
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        const placeholder = document.getElementById('logoPlaceholder');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('d-none');
                
                if (placeholder) {
                    placeholder.classList.add('d-none');
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // When remove logo is checked, hide the preview
    document.addEventListener('DOMContentLoaded', function() {
        const removeLogoCheckbox = document.getElementById('remove_logo');
        
        if (removeLogoCheckbox) {
            removeLogoCheckbox.addEventListener('change', function() {
                const preview = document.getElementById('logoPreview');
                const placeholder = document.getElementById('logoPlaceholder');
                
                if (this.checked) {
                    if (preview) {
                        preview.classList.add('d-none');
                    }
                    
                    if (placeholder) {
                        placeholder.classList.remove('d-none');
                    }
                } else {
                    if (preview.src && preview.src !== '') {
                        preview.classList.remove('d-none');
                        
                        if (placeholder) {
                            placeholder.classList.add('d-none');
                        }
                    }
                }
            });
        }
    });
</script>
@endsection