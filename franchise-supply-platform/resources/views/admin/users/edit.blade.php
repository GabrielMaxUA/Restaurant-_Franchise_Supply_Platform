@extends('layouts.admin')

@section('title', 'Edit User - Restaurant Franchise Supply Platform')

@section('page-title', 'Edit User')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit User: {{ $user->username }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
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
                        <label for="phone">Phone Number</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                            id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
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
            
            <!-- Franchisee details section - will be toggled based on role selection -->
            <div id="franchisee-details" class="mb-4 border rounded p-3 bg-light" style="display: none;">
                <h6 class="mb-3 font-weight-bold">Franchisee Details</h6>
                
                <div class="form-group mb-3">
                    <label for="company_name">Company Name *</label>
                    <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                        id="company_name" name="company_name" value="{{ old('company_name', $user->company_name ?? '') }}">
                    @error('company_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label for="address">Address *</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                        id="address" name="address" rows="3">{{ old('address', $user->address ?? '') }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('role_id');
        const franchiseeDetails = document.getElementById('franchisee-details');
        const companyNameInput = document.getElementById('company_name');
        const addressInput = document.getElementById('address');
        
        // Function to toggle franchisee details based on role selection
        function toggleFranchiseeDetails() {
            const franchiseeRoleId = 2; // Assuming franchisee role ID is 2, adjust as needed
            
            if (roleSelect.value == franchiseeRoleId) {
                franchiseeDetails.style.display = 'block';
                companyNameInput.setAttribute('required', 'required');
                addressInput.setAttribute('required', 'required');
            } else {
                franchiseeDetails.style.display = 'none';
                companyNameInput.removeAttribute('required');
                addressInput.removeAttribute('required');
            }
            
            // Debug
            console.log('Role ID:', roleSelect.value);
            console.log('Franchisee role ID:', franchiseeRoleId);
        }
        
        // Initial check when page loads
        toggleFranchiseeDetails();
        
        // Listen for changes to role select
        roleSelect.addEventListener('change', toggleFranchiseeDetails);
    });
</script>
@endsection