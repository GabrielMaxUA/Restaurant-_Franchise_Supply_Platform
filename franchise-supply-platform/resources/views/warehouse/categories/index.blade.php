@extends('layouts.warehouse')
@section('title', 'Categories - Restaurant Franchise Supply Platform')
@section('page-title', 'Categories')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Category Management</h1>
    <a href="{{ route('warehouse.categories.create') }}" class="btn btn-primary">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td class="text-primary text-decoration-none fw-bold">
                                    {{ $category->name }}
                            </td>
                            <td>{{ Str::limit($category->description, 100) }}</td>
                            <td class="text-center text-decoration-none">
                                        {{ $category->products_count }}
                                        {{ Str::plural('product', $category->products_count) }}
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('warehouse.categories.show', $category->id) }}" class="btn btn-sm btn-info rounded">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('warehouse.categories.edit', $category->id) }}" class="btn btn-sm btn-warning rounded">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('warehouse.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('Are you sure you want to delete this category? This may affect associated products.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                                    <p>No categories found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection