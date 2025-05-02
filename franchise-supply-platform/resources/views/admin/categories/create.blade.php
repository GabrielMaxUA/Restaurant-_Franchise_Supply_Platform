@extends('layouts.admin')

@section('title', 'Add Category - Restaurant Franchise Supply Platform')

@section('page-title', 'Add New Category')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Category Details</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.store') }}" method="POST">
            @csrf
            
            <div class="form-group mb-3">
                <label for="name">Category Name *</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                    id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group mb-3">
                <label for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                    id="description" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Category</button>
            </div>
        </form>
    </div>
</div>
@endsection