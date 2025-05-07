@extends('layouts.admin')

@section('title', 'Add User - Restaurant Franchise Supply Platform')

@section('page-title', 'Add New User')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">User Details</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            
            <!-- Role selection moved to the top -->
            <div class="form-group mb-4">
                <label for="role_id">Role *</label>
                <select class="form-control @error('role_id') is-invalid @enderror" 
                    id="role_id" name="role_id" required>
                    <option value="">-- Select Role --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                            {{ ucfirst($role->name) }}
                        </option>
                    @endforeach
                </select>
                @error('role_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <!-- Franchisee details section - will be toggled based on role selection -->
            <div id="franchisee-details" class="mb-4 border rounded p-3 bg-light" style="display: none;">
                <h6 class="mb-3 font-weight-bold">Franchisee Details</h6>
                
                <div class="form-group mb-3">
                    <label for="company_name">Company Name *</label>
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                        id="company_name" name="company_name" value="{{ old('company_name') }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label for="address">Street Address *</label>
                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                        id="address" name="address" value="{{ old('address') }}">
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                id="city" name="city" value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="state">State/Province *</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                id="state" name="state" value="{{ old('state') }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="postal_code">Postal Code *</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="form-group mb-3">
                    <label for="contact_name">Contact Person</label>
                    <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                        id="contact_name" name="contact_name" value="{{ old('contact_name') }}">
                    @error('contact_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <!-- Common user fields -->
            <h6 class="mb-3 font-weight-bold">User Account Information</h6>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                            id="username" name="username" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                            id="password" name="password" required>
                        <small class="form-text text-muted">Minimum 8 characters</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                            id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
            // Set franchisee role ID to 3 based on your roles table
            let franchiseeRoleId = 3;
            
            console.log('Franchisee role ID:', franchiseeRoleId);
            console.log('Selected Role ID:', roleSelect.value);
            console.log('Is Franchisee:', parseInt(roleSelect.value) === franchiseeRoleId);
            
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
</script>
@endsection