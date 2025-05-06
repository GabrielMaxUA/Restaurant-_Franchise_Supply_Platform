@extends('layouts.warehouse')

@section('title', 'Products - Restaurant Franchise Supply Platform')

@section('page-title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Product Management</h1>
    <a href="{{ route('warehouse.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Product
    </a>
</div>

<!-- Filter Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filter Products</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('warehouse.products.index') }}" method="GET" id="filter-form">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="name" class="form-label">Product Name</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="name" name="name" value="{{ request('name') }}" placeholder="Search by name">
                        @if(request('name'))
                            <button type="button" class="btn btn-outline-secondary clear-input" data-target="name">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="inventory" class="form-label">Inventory Status</label>
                    <select class="form-select" id="inventory" name="inventory">
                        <option value="">All</option>
                        <option value="in_stock" {{ request('inventory') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                        <option value="low_stock" {{ request('inventory') == 'low_stock' ? 'selected' : '' }}>Low Stock (≤ 10)</option>
                        <option value="out_of_stock" {{ request('inventory') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort" class="form-label">Sort By</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price (Low to High)</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price (High to Low)</option>
                        <option value="inventory_asc" {{ request('sort') == 'inventory_asc' ? 'selected' : '' }}>Inventory (Low to High)</option>
                        <option value="inventory_desc" {{ request('sort') == 'inventory_desc' ? 'selected' : '' }}>Inventory (High to Low)</option>
                        <option value="newest" {{ request('sort') == 'newest' || !request('sort') ? 'selected' : '' }}>Newest First</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest First</option>
                    </select>
                </div>
                <div class="col-12">
                    @if(request()->anyFilled(['name', 'category', 'inventory', 'sort']))
                        <a href="{{ route('warehouse.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-undo me-1"></i>Reset All Filters
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results section with filter summary -->
<div class="card shadow">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Product List</h6>
        <span class="text-muted">
            @if(request()->anyFilled(['name', 'category', 'inventory', 'sort']))
                <span class="badge bg-light text-dark border me-1">
                    {{ $products->total() }} products found
                </span>
                @if(request('name'))
                    <span class="badge bg-info text-white me-1">
                        Name: "{{ request('name') }}" <a href="{{ route('warehouse.products.index', request()->except('name')) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('category'))
                    <span class="badge bg-info text-white me-1">
                        Category: {{ $categories->firstWhere('id', request('category'))->name ?? '' }} <a href="{{ route('warehouse.products.index', request()->except('category')) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
                    </span>
                @endif
                @if(request('inventory'))
                    <span class="badge bg-info text-white me-1">
                        Inventory: {{ Str::title(str_replace('_', ' ', request('inventory'))) }} <a href="{{ route('warehouse.products.index', request()->except('inventory')) }}" class="text-white ms-1"><i class="fas fa-times"></i></a>
                    </span>
                @endif
            @else
                <span class="badge bg-light text-dark border">All products: {{ $products->total() }}</span>
            @endif
        </span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Base Price</th>
                        <th>Inventory</th>
                        <th>Variants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
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
                            <td>
                                @if($product->category)
                                    <a href="{{ route('warehouse.products.index', ['category' => $product->category_id]) }}" class="badge bg-info text-decoration-none">
                                        {{ $product->category->name }}
                                    </a>
                                @else
                                    <span class="badge bg-secondary">Uncategorized</span>
                                @endif
                            </td>
                            <td>${{ number_format($product->base_price, 2) }}</td>
                            <td>
                                <span class="badge {{ $product->inventory_count > 10 ? 'bg-success' : ($product->inventory_count > 0 ? 'bg-warning' : 'bg-danger') }}">
                                    {{ $product->inventory_count > 0 ? $product->inventory_count . ' in stock' : 'Out of stock' }}
                                </span>
                            </td>
                            <td>{{ $product->variants->count() }}</td>
                            <td>
                                <div class="d-flex gap-1 justify-content-center">
                                    <a href="{{ route('warehouse.products.show', $product) }}" class="btn btn-sm btn-info rounded" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('warehouse.products.edit', $product) }}" class="btn btn-sm btn-warning rounded" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('warehouse.products.destroy', $product) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger rounded" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-search fa-3x mb-3"></i>
                                    <p class="mb-0">No products found matching your criteria</p>
                                    @if(request()->anyFilled(['name', 'category', 'inventory']))
                                        <a href="{{ route('warehouse.products.index') }}" class="btn btn-sm btn-outline-secondary mt-2">
                                            Clear all filters
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $products->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when select filters change
        const autoSubmitSelects = document.querySelectorAll('#category, #inventory, #sort');
        autoSubmitSelects.forEach(select => {
            select.addEventListener('change', function() {
                document.getElementById('filter-form').submit();
            });
        });
        
        // Add debounce for text search
        const nameInput = document.getElementById('name');
        let searchTimeout;
        
        nameInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                document.getElementById('filter-form').submit();
            }, 500); // 500ms delay
        });
        
        // Clear input button
        document.querySelectorAll('.clear-input').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.target;
                document.getElementById(targetId).value = '';
                document.getElementById('filter-form').submit();
            });
        });
    });
</script>
@endsection