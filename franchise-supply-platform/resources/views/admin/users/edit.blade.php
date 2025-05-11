@extends('layouts.admin')

@section('title', 'Edit User - Restaurant Franchise Supply Platform')

@section('page-title', 'Edit User')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit User: {{ $user->username }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Role selection moved to the top -->
            <div class="form-group mb-4">
                <label for="role_id">Role *</label>
                <select class="form-control @error('role_id') is-invalid @enderror" 
                    id="role_id" name="role_id" required>
                    <option value="">-- Select Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" 
                            {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- User Status Toggle -->
            @if($user->id !== auth()->id())
                <div class="form-group mb-4">
                    <label for="status">Account Status</label>
                    <div class="form-check form-switch">
                        <!-- The key change is here - we need to keep status in the request even when unchecked -->
                        <input type="hidden" name="status" value="0">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" 
                            {{ $user->isActive() ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">
                            <span id="status-text" class="{{ $user->isActive() ? 'text-success' : 'text-danger' }}">
                                {{ $user->isActive() ? 'Active' : 'Blocked' }}
                            </span>
                        </label>
                    </div>
                    <small class="form-text text-muted">Toggle to activate or block this user account</small>
                </div>
            @else
                <div class="form-group mb-4">
                    <label for="status">Account Status</label>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>You cannot change your own account status
                    </div>
                    <input type="hidden" name="status" value="1">
                </div>
            @endif
            
            <!-- Franchisee details section - will be toggled based on role selection -->
            <div id="franchisee-details" class="mb-4 border rounded p-3 bg-light" style="display: none;">
                <h6 class="mb-3 font-weight-bold">Franchisee Details</h6>
                
                <div class="form-group mb-3">
                    <label for="company_name">Company Name *</label>
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                        id="company_name" name="company_name" 
                        value="{{ old('company_name', $user->franchiseeProfile->company_name ?? '') }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label for="address">Street Address *</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                        id="address" name="address" 
                        value="{{ old('address', $user->franchiseeProfile->address ?? '') }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                id="city" name="city" 
                                value="{{ old('city', $user->franchiseeProfile->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="state">State/Province *</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                id="state" name="state" 
                                value="{{ old('state', $user->franchiseeProfile->state ?? '') }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="postal_code">Postal Code *</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                id="postal_code" name="postal_code" 
                                value="{{ old('postal_code', $user->franchiseeProfile->postal_code ?? '') }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="contact_name">Contact Person</label>
                    <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                        id="contact_name" name="contact_name" 
                        value="{{ old('contact_name', $user->franchiseeProfile->contact_name ?? '') }}">
                    @error('contact_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Company Logo Upload -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="logo">Company Logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                id="logo" name="logo" accept="image/*">
                            <small class="form-text text-muted">Allowed formats: JPEG, PNG, JPG, GIF. Max size: 2MB.</small>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-center">
                            <div class="mb-2">Logo Preview:</div>
                            <div class="logoBox">
                                @if($user->franchiseeProfile && $user->franchiseeProfile->logo_path)
                                    <!-- Show existing logo and preview for new uploads -->
                                    <img id="logo-preview" src="{{ asset('storage/' . $user->franchiseeProfile->logo_path) }}" 
                                        alt="Company Logo" class="img-thumbnail" style="max-height: 100px;">
                                @else
                                    <!-- Just show preview for new uploads -->
                                    <img id="logo-preview" src="#" alt="Logo Preview" 
                                        class="img-thumbnail" style="max-height: 100px; display: none;">
                                    <div id="no-logo-selected" class="text-muted">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <p>No logo uploaded</p>
                                    </div>
                                @endif
                            </div>
                            @if($user->franchiseeProfile && $user->franchiseeProfile->logo_path)
                                <div class="mt-2 deleteLogo">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo">
                                        <label class="form-check-label" for="remove_logo">
                                            Remove current logo
                                        </label>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Common user fields -->
            <h6 class="mb-3 font-weight-bold">User Account Information</h6>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                            id="username" name="username" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                            id="password" name="password">
                        <small class="form-text text-muted">Leave blank to keep current password</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" class="form-control" 
                            id="password_confirmation" name="password_confirmation">
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                    id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<style>
  .logoBox{
    width: auto;
    height: auto;
  }

  .deleteLogo{
    width: 50%;
    margin: 0 auto;
    display: flex;
    justify-content: center;
  }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Status toggle functionality
        const statusToggle = document.getElementById('status');
        const statusText = document.getElementById('status-text');
        
        if (statusToggle) {
            statusToggle.addEventListener('change', function() {
                if (this.checked) {
                    statusText.textContent = 'Active';
                    statusText.classList.remove('text-danger');
                    statusText.classList.add('text-success');
                } else {
                    statusText.textContent = 'Blocked';
                    statusText.classList.remove('text-success');
                    statusText.classList.add('text-danger');
                }
            });
        }

        const roleSelect = document.getElementById('role_id');
        const franchiseeDetails = document.getElementById('franchisee-details');
        const requiredFranchiseeFields = [
            document.getElementById('company_name'),
            document.getElementById('address'),
            document.getElementById('city'),
            document.getElementById('state'),
            document.getElementById('postal_code')
        ];
        
        // Function to toggle franchisee details based on role selection
        function toggleFranchiseeDetails() {
            // Set franchisee role ID to 3 based on your controller code
            let franchiseeRoleId = 3;
            
            if (parseInt(roleSelect.value) === franchiseeRoleId) {
                franchiseeDetails.style.display = 'block';
                
                // Make franchisee fields required
                requiredFranchiseeFields.forEach(field => {
                    if (field) field.setAttribute('required', 'required');
                });
            } else {
                franchiseeDetails.style.display = 'none';
                
                // Remove required attribute from franchisee fields
                requiredFranchiseeFields.forEach(field => {
                    if (field) field.removeAttribute('required');
                });
            }
        }
        
        // Initial check when page loads
        toggleFranchiseeDetails();
        
        // Listen for changes to role select
        roleSelect.addEventListener('change', toggleFranchiseeDetails);
    });

    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logo-preview');
    const noLogoSelected = document.getElementById('no-logo-selected');
    const removeLogoCheckbox = document.getElementById('remove_logo');
    
    // Function to handle image preview
    function handleImagePreview(e) {
        const file = e.target.files[0];
        
        if (file) {
            // Create a file reader
            const reader = new FileReader();
            
            // Set up the reader to display the image
            reader.onload = function(e) {
                logoPreview.src = e.target.result;
                logoPreview.style.display = 'block';
                
                // Hide the "no logo" message
                if (noLogoSelected) {
                    noLogoSelected.style.display = 'none';
                }
            }
            
            // Read the file as a data URL
            reader.readAsDataURL(file);
        } else {
            // If no file is selected, show the "no logo" message
            if (noLogoSelected) {
                logoPreview.style.display = 'none';
                noLogoSelected.style.display = 'block';
            }
        }
    }
    
    // Add event listener to the file input
    if (logoInput) {
        logoInput.addEventListener('change', handleImagePreview);
    }
    
    // Handle the "remove logo" checkbox (for edit page only)
    if (removeLogoCheckbox) {
        removeLogoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // If checked, hide the logo preview and show the "no logo" message
                logoPreview.style.display = 'none';
                if (noLogoSelected) {
                    noLogoSelected.style.display = 'block';
                }
            } else {
                // If unchecked and there's a logo, show it again
                if (logoPreview.getAttribute('src') !== '#') {
                    logoPreview.style.display = 'block';
                    if (noLogoSelected) {
                        noLogoSelected.style.display = 'none';
                    }
                }
            }
        });
    }
</script>
@endsection