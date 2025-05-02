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
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection