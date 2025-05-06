@extends('layouts.warehouse')

@section('title', 'Edit Category - Restaurant Franchise Supply Platform')

@section('page-title', 'Edit Category')

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Categories
    </a>
</div>

<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Edit Category: {{ $category->name }}</h6>
    </div>
    <div class="card-body">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('warehouse.categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" 
                       value="{{ old('name', $category->name) }}" required>
                <div class="form-text">Enter a unique name for this category</div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" 
                          rows="4">{{ old('description', $category->description) }}</textarea>
                <div class="form-text">Provide a description of what types of products belong in this category</div>
            </div>
            
            <div class="d-flex justify-content-between">
                <div>
                    <!-- Current product count information -->
                    @if($category->products_count > 0 || $category->products->count() > 0)
                        <span class="text-info">
                            <i class="fas fa-info-circle me-1"></i>
                            This category is currently assigned to 
                            <strong>{{ $category->products_count ?? $category->products->count() }}</strong> 
                            {{ Str::plural('product', $category->products_count ?? $category->products->count()) }}
                        </span>
                    @endif
                </div>
                <div>
                    <a href="{{ route('warehouse.categories.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if(isset($category->products) && $category->products->count() > 0)
<div class="card shadow mt-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Products in this Category</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Product Name</th>
                        <th>Base Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($category->products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>
                        <td>{{ $product->name }}</td>
                        <td>${{ number_format($product->base_price, 2) }}</td>
                        <td>
                            <a href="{{ route('warehouse.products.edit', $product) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('warehouse.products.show', $product) }}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection