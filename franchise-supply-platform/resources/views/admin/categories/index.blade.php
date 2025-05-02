@extends('layouts.admin')

@section('title', 'Categories - Restaurant Franchise Supply Platform')

@section('page-title', 'Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Category Management</h1>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Category
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ Str::limit($category->description, 100) }}</td>
                            <td>{{ $category->products_count ?? 0 }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No categories found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection