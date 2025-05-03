@extends('layouts.admin')

@section('title', $category->name . ' - Restaurant Franchise Supply Platform')

@section('page-title', 'Category Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Categories
    </a>
</div>

<!-- Category Information Card -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Category Information</h6>
        <div>
            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i> Edit Category
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 fw-bold">Category ID:</div>
            <div class="col-md-9">{{ $category->id }}</div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3 fw-bold">Name:</div>
            <div class="col-md-9">{{ $category->name }}</div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3 fw-bold">Description:</div>
            <div class="col-md-9">
                {!! nl2br(e($category->description)) ?? '<span class="text-muted">No description provided</span>' !!}
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3 fw-bold">Created:</div>
            <div class="col-md-9">{{ $category->created_at }}</div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3 fw-bold">Last Updated:</div>
            <div class="col-md-9">{{ $category->updated_at }}</div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3 fw-bold">Products Count:</div>
            <div class="col-md-9">
                <span class="badge {{ $category->products->count() > 0 ? 'bg-primary' : 'bg-secondary' }} fs-6">
                    {{ $category->products->count() }} {{ Str::plural('product', $category->products->count()) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Category Products -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Products in this Category</h6>
    </div>
    <div class="card-body">
        @if($category->products->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>Image</th>
                            <th>Name</th>
                            <th>Base Price</th>
                            <th>Inventory</th>
                            <th>Variants</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($category->products as $product)
                            <tr class="text-center align-middle">
                                <td>
                                    @if($product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $product->images->first()->image_url) }}"
                                             alt="{{ $product->name }}"
                                             width="75" height="75"
                                             class="img-thumbnail">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                             style="width: 75px; height: 75px; margin: 0 auto;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>${{ number_format($product->base_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $product->inventory_count > 10 ? 'bg-success' : ($product->inventory_count > 0 ? 'bg-warning' : 'bg-danger') }}">
                                        {{ $product->inventory_count > 0 ? $product->inventory_count . ' in stock' : 'Out of stock' }}
                                    </span>
                                </td>
                                <td>{{ $product->variants->count() }}</td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info rounded">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning rounded">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('Are you sure you want to delete this product?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <p class="text-muted">No products in this category</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-2">
                    <i class="fas fa-plus me-1"></i> Add New Product
                </a>
            </div>
        @endif
    </div>
</div>
@endsection