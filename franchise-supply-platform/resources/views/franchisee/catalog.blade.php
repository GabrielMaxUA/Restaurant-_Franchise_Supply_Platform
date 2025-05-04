@extends('layouts.franchisee')

@section('title', 'Product Catalog - Franchisee Portal')

@section('page-title', 'Product Catalog')

@section('styles')
<style>
/* Main styling for Amazon-style product modal */
.product-image {
    height: 60px;
    width: 60px;
    object-fit: cover;
}

.sticky-categories {
    position: sticky;
    top: 20px;
}

.product-table th {
    background-color: #f8f9fa;
}

.product-table .product-name {
    font-weight: 500;
}

.favorite-icon {
    cursor: pointer;
}

.favorite-icon.active {
    color: #dc3545;
}

.product-description {
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.status-in-stock {
    color: #198754;
}

.status-low-stock {
    color: #ffc107;
}

.status-out-of-stock {
    color: #dc3545;
}

/* Styling for product thumbnails */
.thumbnail-wrapper {
    width: 75px !important;
    height: 75px !important;
    overflow: hidden;
    border-radius: 4px;
    border: 2px solid transparent;
    transition: all 0.2s ease;
    position: relative;
    cursor: pointer;
    margin: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.thumbnail-wrapper:hover {
    transform: scale(1.05);
    border-color: #28a745;
    box-shadow: 0 3px 6px rgba(0,0,0,0.15);
}

.thumbnail-wrapper.active-thumb {
    border-color: #4e73df;
    box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.5);
}

.product-thumbnail, .variant-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.2s ease;
}

.thumbnails-container {
    padding: 5px;
    background-color: #f8f9fa;
    border-radius: 5px;
    overflow-x: auto;
    min-height: 85px;
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Variant label for thumbnails */
.variant-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.6);
    color: white;
    font-size: 10px;
    padding: 2px 4px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    opacity: 0.9;
    transition: all 0.2s ease;
}

.variant-thumb:hover .variant-label {
    opacity: 1;
    background: rgba(0,0,0,0.8);
}

/* Fade effect for content changes */
.main-display-content {
    transition: opacity 0.3s ease;
}

.fade-content {
    opacity: 0;
}

/* Custom quantity input styling */
.quantity-input {
    /* Hide the default up/down arrows */
    -moz-appearance: textfield;
}

.quantity-input::-webkit-outer-spin-button,
.quantity-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Amazon-style add to cart section */
.add-to-cart-section {
    margin-top: 1rem;
}

.add-to-cart-btn {
    padding: 0.75rem;
    font-size: 1.1rem;
}

/* Styling for the main product image */
.main-product-image {
    border: 1px solid #e3e6f0;
    border-radius: 4px;
    padding: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #fff;
}

.main-product-image img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
}

/* Variants table styling */
.table-bordered {
    border-color: #e3e6f0;
}

.table-hover tbody tr:hover {
    background-color: rgba(40, 167, 69, 0.05);
}

.table-light th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom-width: 1px;
}

/* Status badges */
.badge.bg-success, 
.badge.bg-warning, 
.badge.bg-danger,
.badge.bg-secondary {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

/* Improve modal layout */
.modal-lg {
    max-width: 900px;
}

.modal-body {
    padding: 1.5rem;
}

.modal-header {
    border-bottom: 1px solid #e3e6f0;
    background-color: #f8f9fa;
}

.modal-footer {
    border-top: 1px solid #e3e6f0;
    background-color: #f8f9fa;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .thumbnails-container {
        justify-content: flex-start !important;
    }
    
    .thumbnail-wrapper {
        width: 65px !important;
        height: 65px !important;
    }
    
    .modal-body .row {
        flex-direction: column;
    }
    
    .modal-body .col-md-5,
    .modal-body .col-md-7 {
        width: 100%;
    }
    
    .modal-body .col-md-5 {
        margin-bottom: 1rem;
    }
    
    .main-product-image {
        height: 250px !important;
    }
}
</style>
@endsection

@section('content')
<!-- Search & Filter Section -->
<div class="row mb-4">
    <div class="col-md-6">
        <form action="{{ route('franchisee.catalog') }}" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Search products..." name="search" value="{{ request('search') }}">
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-end">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-sort me-1"></i> Sort By
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item {{ request('sort') == 'name_asc' ? 'active' : '' }}" href="{{ route('franchisee.catalog', ['sort' => 'name_asc']) }}">Name (A-Z)</a></li>
                <li><a class="dropdown-item {{ request('sort') == 'name_desc' ? 'active' : '' }}" href="{{ route('franchisee.catalog', ['sort' => 'name_desc']) }}">Name (Z-A)</a></li>
                <li><a class="dropdown-item {{ request('sort') == 'price_asc' ? 'active' : '' }}" href="{{ route('franchisee.catalog', ['sort' => 'price_asc']) }}">Price (Low to High)</a></li>
                <li><a class="dropdown-item {{ request('sort') == 'price_desc' ? 'active' : '' }}" href="{{ route('franchisee.catalog', ['sort' => 'price_desc']) }}">Price (High to Low)</a></li>
                <li><a class="dropdown-item {{ request('sort') == 'popular' ? 'active' : '' }}" href="{{ route('franchisee.catalog', ['sort' => 'popular']) }}">Most Popular</a></li>
            </ul>
        </div>
        <button type="button" class="btn btn-outline-secondary ms-2" id="toggleFilters">
            <i class="fas fa-filter me-1"></i> Filters
        </button>
    </div>
</div>

<!-- Advanced Filters (Hidden by Default) -->
<div class="row mb-4" id="advancedFilters" style="display: none;">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('franchisee.catalog') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="price_min" class="form-label">Min Price</label>
                                <input type="number" class="form-control" id="price_min" name="price_min" value="{{ request('price_min') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="price_max" class="form-label">Max Price</label>
                                <input type="number" class="form-control" id="price_max" name="price_max" value="{{ request('price_max') }}" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="stock_status" class="form-label">Stock Status</label>
                                <select class="form-select" id="stock_status" name="stock_status">
                                    <option value="">All</option>
                                    <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                    <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('franchisee.catalog') }}" class="btn btn-outline-secondary me-2">Reset</a>
                        <button type="submit" class="btn btn-success">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Categories Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="card sticky-categories">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-tags me-2"></i> Categories</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="{{ route('franchisee.catalog') }}" class="list-group-item list-group-item-action {{ !request('category') ? 'active' : '' }}">
                        All Products <span class="badge bg-success float-end">{{ $total_products }}</span>
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('franchisee.catalog', ['category' => $category->id]) }}" 
                           class="list-group-item list-group-item-action {{ request('category') == $category->id ? 'active' : '' }}">
                            {{ $category->name }} 
                            <span class="badge bg-secondary float-end">{{ $category->products_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="card-footer">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="show_favorites" name="favorites" 
                           {{ request('favorites') ? 'checked' : '' }} 
                           onchange="window.location.href='{{ route('franchisee.catalog', array_merge(request()->except('favorites'), request('favorites') ? [] : ['favorites' => 1])) }}'">
                    <label class="form-check-label" for="show_favorites">
                        <i class="fas fa-heart text-danger me-1"></i> Show favorites only
                    </label>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="col-md-9">
        @if($products->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No products found matching your criteria. Try adjusting your filters or search terms.
            </div>
        @else
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover product-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Image</th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr class="product-row" data-product-id="{{ $product->id }}">
                                    <td>
                                        @if($product->images && $product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                                 class="img-thumbnail product-image" alt="{{ $product->name }}">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center product-image">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="product-name">{{ $product->name }}</div>
                                        <div class="small text-muted product-description">{{ $product->description }}</div>
                                        <div class="small text-muted">{{ $product->unit_size }} {{ $product->unit_type }}</div>
                                        @if($product->is_new)
                                            <span class="badge bg-info">New</span>
                                        @endif
                                        @if($product->is_featured)
                                            <span class="badge bg-warning">Featured</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                                    <td><strong>${{ number_format($product->price, 2) }}</strong></td>
                                    <td>
                                        @if($product->inventory_count > 10)
                                            <span class="status-in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                        @elseif($product->inventory_count > 0)
                                            <span class="status-low-stock"><i class="fas fa-exclamation-circle me-1"></i> Low Stock</span>
                                        @else
                                            <span class="status-out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-between align-items-center">
                                            <form action="{{ route('franchisee.cart.add') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-success rounded" {{ $product->inventory_count <= 0 ? 'disabled' : '' }} title="Add to Cart">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </form>
                                            
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded product-favorite" data-product-id="{{ $product->id }}" title="{{ $product->is_favorite > 0 ? 'Remove from Favorites' : 'Add to Favorites' }}">
                                                    <i class="fas fa-heart {{ $product->is_favorite > 0 ? 'text-danger' : '' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info rounded view-product-btn" data-bs-toggle="modal" data-bs-target="#productModal{{ $product->id }}" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $products->appends(request()->all())->links() }}
                    </div>
                </div>
            </div>
            
            <!-- Product Modals -->
            @foreach($products as $product)
            <!-- Product Modal with Amazon-Style UI and Fixed Thumbnails -->
            <div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1" aria-labelledby="productModalLabel{{ $product->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalLabel{{ $product->id }}">
                                <span class="main-product-name">{{ $product->name }}</span>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Main Product/Variant Display Section -->
                            <div class="row">
                                <div class="col-md-5 position-relative">
                                    <div class="main-product-image" style="height: 300px;">
                                        @if($product->images && $product->images->count() > 0)
                                            <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                                class="img-fluid rounded w-100 h-100" style="object-fit: contain;" 
                                                alt="{{ $product->name }}" id="mainDisplayImage{{ $product->id }}">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center h-100">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <!-- Thumbnails Row -->
                                    <div class="product-thumbnails mt-3">
                                        <div class="d-flex flex-wrap justify-content-center gap-2 thumbnails-container">
                                            <!-- Product Thumbnails -->
                                            @if($product->images && $product->images->count() > 0)
                                                @foreach($product->images as $index => $image)
                                                    <div class="thumbnail-wrapper product-thumb {{ $index === 0 ? 'active-thumb' : '' }}" 
                                                         data-product-id="{{ $product->id }}" 
                                                         data-image-url="{{ asset('storage/' . $image->image_url) }}">
                                                        <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                             alt="{{ $product->name }} thumbnail {{ $index + 1 }}"
                                                             class="product-thumbnail">
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <!-- Dynamic content that changes based on selection -->
                                    <div class="main-display-content">
                                        <h4 class="main-display-name">{{ $product->name }}</h4>
                                        <div class="d-flex align-items-center mb-3">
                                            <span class="badge bg-secondary me-2">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                            <span class="text-muted"><i class="fas fa-building me-1"></i> {{ $product->supplier->name ?? 'Unknown Supplier' }}</span>
                                        </div>
                                        
                                        <div class="d-flex align-items-center mb-3 main-display-price">
                                            <h3 class="me-2 current-price">${{ number_format($product->price, 2) }}</h3>
                                            @if(isset($product->compare_price) && $product->compare_price > $product->price)
                                                <span class="text-muted text-decoration-line-through">${{ number_format($product->compare_price, 2) }}</span>
                                                <span class="badge bg-danger ms-2">{{ round((1 - $product->price / $product->compare_price) * 100) }}% OFF</span>
                                            @endif
                                        </div>
                                        
                                        <div class="mb-3 main-display-stock">
                                            @if($product->inventory_count > 10)
                                                <span class="badge bg-success">In Stock</span>
                                            @elseif($product->inventory_count > 0)
                                                <span class="badge bg-warning text-dark">Low Stock</span>
                                            @else
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @endif
                                            
                                            <span class="ms-2 inventory-count">{{ $product->inventory_count ?? 0 }} left</span>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <p class="main-display-description">{{ $product->description }}</p>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <p class="mb-1"><strong>Product Details:</strong></p>
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-weight me-2"></i> <span class="unit-details">{{ $product->unit_size ?? '' }} {{ $product->unit_type ?? '' }}</span></li>
                                                <li><i class="fas fa-box me-2"></i> SKU: <span class="sku-details">{{ $product->sku ?? 'N/A' }}</span></li>
                                                @if(isset($product->min_order_quantity) && $product->min_order_quantity > 0)
                                                    <li><i class="fas fa-truck-loading me-2"></i> Min Order: <span class="min-order">{{ $product->min_order_quantity }}</span></li>
                                                @endif
                                            </ul>
                                        </div>
                                        
                                        <!-- Add to Cart Form - Simplified and Amazon-like -->
                                        <div class="add-to-cart-section border-top pt-3">
                                            <form action="{{ route('franchisee.cart.add') }}" method="POST" class="d-flex flex-column" id="addToCartForm{{ $product->id }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="variant_id" value="" class="selected-variant-id">
                                                
                                                <div class="d-flex align-items-center mb-3">
                                                    <label class="me-3">Quantity:</label>
                                                    <div class="input-group" style="width: 130px;">
                                                        <button type="button" class="btn btn-outline-secondary quantity-decrement">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" name="quantity" value="1" min="1" max="{{ $product->inventory_count }}" 
                                                               class="form-control text-center quantity-input" 
                                                               style="width: 50px;"
                                                               inputmode="numeric">
                                                        <button type="button" class="btn btn-outline-secondary quantity-increment">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <div class="d-grid gap-2">
                                                    <button type="submit" class="btn btn-success btn-lg add-to-cart-btn" {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-outline-secondary product-favorite" data-product-id="{{ $product->id }}">
                                                        <i class="fas fa-heart {{ $product->is_favorite > 0 ? 'text-danger' : '' }}"></i> 
                                                        {{ $product->is_favorite > 0 ? 'Remove from Favorites' : 'Add to Favorites' }}
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        
                                        <!-- Will be shown when viewing a variant -->
                                        <div class="variant-specific-details mt-3" style="display: none;">
                                            <div class="alert alert-light border">
                                                <div class="d-flex">
                                                    <button class="btn btn-outline-secondary btn-sm me-2 return-to-main-product" data-product-id="{{ $product->id }}">
                                                        <i class="fas fa-arrow-left"></i> Back to main product
                                                    </button>
                                                    <div>
                                                        <p class="mb-0">Currently viewing:</p>
                                                        <p class="mb-0 fw-bold current-variant-name"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Variants Section -->
                            @if($product->variants && $product->variants->count() > 0)
                            <div class="row mt-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <span class="variants-section-title">Choose a Variant</span>
                                    </h5>
                                    
                                    <!-- Variants Table -->
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 60px;">Image</th>
                                                    <th>Variant</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th style="width: 100px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="variants-container">
                                                <!-- Original Product Row (Hidden by default, shown when viewing a variant) -->
                                                <tr class="main-product-row" style="display: none;" data-item-id="main-{{ $product->id }}">
                                                    <td class="text-center">
                                                        @if($product->images && $product->images->count() > 0)
                                                            <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                                                class="img-thumbnail product-thumbnail" 
                                                                style="height: 50px; width: 50px; object-fit: cover;"
                                                                alt="{{ $product->name }}">
                                                        @else
                                                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="fw-bold">{{ $product->name }}</span>
                                                        <div class="badge bg-secondary">Original Product</div>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">${{ number_format($product->price, 2) }}</div>
                                                    </td>
                                                    <td>
                                                        @if($product->inventory_count > 10)
                                                            <span class="status-in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                                        @elseif($product->inventory_count > 0)
                                                            <span class="status-low-stock"><i class="fas fa-exclamation-circle me-1"></i> Low Stock</span>
                                                        @else
                                                            <span class="status-out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-primary w-100 select-item" 
                                                                data-item-id="main-{{ $product->id }}"
                                                                data-name="{{ $product->name }}"
                                                                data-description="{{ $product->description }}"
                                                                data-price="{{ $product->price }}"
                                                                data-inventory="{{ $product->inventory_count }}"
                                                                data-image-url="{{ $product->images && $product->images->count() > 0 ? asset('storage/' . $product->images->first()->image_url) : '' }}"
                                                                data-is-main="true">
                                                            <i class="fas fa-exchange-alt me-1"></i> Select
                                                        </button>
                                                    </td>
                                                </tr>
                                                
                                                <!-- Variant Rows -->
                                               <!-- Updated section to allow serialized variant images in data attributes -->
                                              @foreach($product->variants as $variant)
                                              <tr class="variant-row" data-item-id="variant-{{ $variant->id }}">
                                                  <td class="text-center">
                                                      @if($variant->images && $variant->images->count() > 0)
                                                          <img src="{{ asset('storage/' . $variant->images->first()->image_url) }}" 
                                                              class="img-thumbnail variant-thumbnail" 
                                                              style="height: 50px; width: 50px; object-fit: cover;"
                                                              alt="{{ $variant->name }}">
                                                      @elseif($variant->image_url)
                                                          <img src="{{ asset('storage/' . $variant->image_url) }}" 
                                                              class="img-thumbnail variant-thumbnail" 
                                                              style="height: 50px; width: 50px; object-fit: cover;"
                                                              alt="{{ $variant->name }}">
                                                      @else
                                                          <div class="bg-light d-flex align-items-center justify-content-center" style="height: 50px; width: 50px;">
                                                              <i class="fas fa-image text-muted"></i>
                                                          </div>
                                                      @endif
                                                  </td>
                                                  <td>{{ $variant->name }}</td>
                                                  <td>
                                                      <div class="fw-bold">${{ number_format($product->price + $variant->price_adjustment, 2) }}</div>
                                                      @if($variant->price_adjustment != 0)
                                                          <small class="text-muted">
                                                              @if($variant->price_adjustment > 0)
                                                                  <span class="text-success">+${{ number_format($variant->price_adjustment, 2) }}</span>
                                                              @else
                                                                  <span class="text-danger">-${{ number_format(abs($variant->price_adjustment), 2) }}</span>
                                                              @endif
                                                          </small>
                                                      @endif
                                                  </td>
                                                  <td>
                                                      @if($variant->inventory_count > 10)
                                                          <span class="status-in-stock"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                                      @elseif($variant->inventory_count > 0)
                                                          <span class="status-low-stock"><i class="fas fa-exclamation-circle me-1"></i> Low Stock</span>
                                                      @else
                                                          <span class="status-out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                                      @endif
                                                  </td>
                                                  <td>
                                                      <button type="button" class="btn btn-sm btn-primary w-100 select-item" 
                                                              data-item-id="variant-{{ $variant->id }}"
                                                              data-name="{{ $variant->name }}"
                                                              data-description="{{ $variant->description ?? $product->description }}"
                                                              data-price="{{ $product->price + $variant->price_adjustment }}"
                                                              data-inventory="{{ $variant->inventory_count }}"
                                                              data-image-url="{{ $variant->images && $variant->images->count() > 0 ? asset('storage/' . $variant->images->first()->image_url) : ($variant->image_url ? asset('storage/' . $variant->image_url) : '') }}"
                                                              data-variant-id="{{ $variant->id }}"
                                                              data-has-images="{{ ($variant->images && $variant->images->count() > 0) || $variant->image_url ? 'true' : 'false' }}"
                                                              @if($variant->images && $variant->images->count() > 0)
                                                              data-variant-images="{{ json_encode($variant->images->map(function($img) { 
                                                                  return [
                                                                      'id' => $img->id, 
                                                                      'image_url' => asset('storage/' . $img->image_url)
                                                                  ]; 
                                                              })) }}"
                                                              @endif
                                                              >
                                                          <i class="fas fa-exchange-alt me-1"></i> Select
                                                      </button>
                                                  </td>
                                              </tr>
                                              @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
// Toggle advanced filters
document.getElementById('toggleFilters').addEventListener('click', function() {
    const filtersSection = document.getElementById('advancedFilters');
    filtersSection.style.display = filtersSection.style.display === 'none' ? 'block' : 'none';
});

document.addEventListener('DOMContentLoaded', function() {
    // Process all product modals on the page for variant swapping
    document.querySelectorAll('[id^="productModal"]').forEach(modal => {
        const productId = modal.id.replace('productModal', '');
        setupVariantSwapping(modal, productId);
    });
    
    // Handle favorite toggling
    const favoriteButtons = document.querySelectorAll('.product-favorite');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            // Select all heart icons for this product ID
            const icons = document.querySelectorAll('[data-product-id="' + productId + '"] .fa-heart');
            
            fetch('{{ route('franchisee.toggle.favorite') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle the favorite icon on all instances (table and modal)
                    icons.forEach(icon => {
                        if (data.is_favorite) {
                            icon.classList.add('text-danger');
                            showFloatingAlert('Product added to favorites!', 'success');
                        } else {
                            icon.classList.remove('text-danger');
                            showFloatingAlert('Product removed from favorites.', 'info');
                        }
                    });
                    
                    // Update button text in modals
                    const modalButtons = document.querySelectorAll('.product-favorite[data-product-id="' + productId + '"]');
                    modalButtons.forEach(btn => {
                        // Find text node that might follow the icon
                        const textContent = btn.innerHTML;
                        if (textContent.includes('Add to Favorites') || textContent.includes('Remove from Favorites')) {
                            // Update the text based on favorite status
                            if (data.is_favorite) {
                                btn.innerHTML = btn.innerHTML.replace('Add to Favorites', 'Remove from Favorites');
                            } else {
                                btn.innerHTML = btn.innerHTML.replace('Remove from Favorites', 'Add to Favorites');
                            }
                        }
                    });
                    
                    // If showing favorites only and removed from favorites, hide this product
                    if (!data.is_favorite && window.location.search.includes('favorites=1')) {
                        const productRow = document.querySelector('tr.product-row[data-product-id="' + productId + '"]');
                        if (productRow) {
                            productRow.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showFloatingAlert('Failed to update favorites. Please try again.', 'danger');
            });
        });
    });
});

// Setup quantity buttons with proper increment/decrement by 1
function setupQuantityButtons(modal) {
    const quantityInput = modal.querySelector('.quantity-input');
    const decrementBtn = modal.querySelector('.quantity-decrement');
    const incrementBtn = modal.querySelector('.quantity-increment');
    
    if (!quantityInput || !decrementBtn || !incrementBtn) return;
    
    // Disable default browser controls
    quantityInput.style.MozAppearance = 'textfield';
    quantityInput.style.appearance = 'textfield';
    
    // Remove existing event listeners (if any)
    const newDecrementBtn = decrementBtn.cloneNode(true);
    const newIncrementBtn = incrementBtn.cloneNode(true);
    decrementBtn.parentNode.replaceChild(newDecrementBtn, decrementBtn);
    incrementBtn.parentNode.replaceChild(newIncrementBtn, incrementBtn);
    
    // Set up new event listeners
    newDecrementBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let value = parseInt(quantityInput.value) || 1;
        const min = parseInt(quantityInput.min) || 1;
        if (value > min) {
            quantityInput.value = value - 1;
        }
        return false;
    });
    
    newIncrementBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        let value = parseInt(quantityInput.value) || 1;
        const max = parseInt(quantityInput.max) || 999;
        if (value < max) {
            quantityInput.value = value + 1;
        }
        return false;
    });
    
    // Prevent default spinner behavior
    quantityInput.addEventListener('keydown', function(e) {
        if (e.which === 38 || e.which === 40) {
            e.preventDefault();
        }
    });
}

// Setup variant swapping functionality for a specific modal
function setupVariantSwapping(modal, productId) {
    // Main elements
    const mainImage = modal.querySelector('#mainDisplayImage' + productId);
    const selectButtons = modal.querySelectorAll('.select-item');
    const returnToMainButton = modal.querySelector('.return-to-main-product');
    const mainProductRow = modal.querySelector('.main-product-row');
    const variantRows = modal.querySelectorAll('.variant-row');
    const variantSpecificDetails = modal.querySelector('.variant-specific-details');
    const mainProductCTA = modal.querySelector('.add-to-cart-section');
    const currentVariantName = modal.querySelector('.current-variant-name');
    const variantsSectionTitle = modal.querySelector('.variants-section-title');
    const quantityInput = modal.querySelector('.quantity-input');
    const addToCartBtn = modal.querySelector('.add-to-cart-btn');
    const addToCartForm = modal.querySelector('form');
    const selectedVariantIdInput = modal.querySelector('.selected-variant-id');
    
    // Thumbnail container
    const thumbnailsContainer = modal.querySelector('.thumbnails-container');
    const productThumbs = modal.querySelectorAll('.product-thumb');
    
    if (!mainImage) return; // Skip if essential elements not found
    
    // Initialize with main product as default
    let currentlyViewingMain = true;
    let currentVariantId = null;
    
    // Store original thumbnails HTML for later use
    if (thumbnailsContainer) {
        thumbnailsContainer.dataset.originalHtml = thumbnailsContainer.innerHTML;
    }
    
    // Fix the blinking issue when modal opens by preloading content
    modal.addEventListener('show.bs.modal', function() {
        // Always reset to main product view when modal is going to be shown
        // This ensures that even if modal was closed while viewing a variant, 
        // it opens with the main product view
        switchToMainProduct();
    });
    
    // Setup quantity buttons after modal is fully shown
    modal.addEventListener('shown.bs.modal', function() {
        setupQuantityButtons(modal);
    });
    
    // Store original values
    const originalValues = {
        name: modal.querySelector('.main-display-name').textContent,
        description: modal.querySelector('.main-display-description').textContent,
        price: parseFloat(modal.querySelector('.current-price').textContent.replace('$', '')),
        imageUrl: mainImage ? mainImage.src : '',
        inventoryCount: parseInt(modal.querySelector('.inventory-count').textContent),
        maxQuantity: parseInt(quantityInput.getAttribute('max'))
    };
    
    // Setup event listeners for product thumbnails
    setupThumbnailClickHandlers(thumbnailsContainer, mainImage);
    
    // Setup event listeners for all variant select buttons
    selectButtons.forEach(button => {
        button.addEventListener('click', function() {
            const isMain = this.dataset.isMain === 'true';
            
            if (isMain) {
                // Switch back to main product
                switchToMainProduct();
            } else {
                // Switch to variant
                const variantId = this.dataset.variantId;
                const hasImages = this.dataset.hasImages === 'true';
                
                // Get variant data for display
                const variantData = {
                    name: this.dataset.name,
                    description: this.dataset.description,
                    price: parseFloat(this.dataset.price),
                    imageUrl: this.dataset.imageUrl,
                    inventoryCount: parseInt(this.dataset.inventory),
                    variantId: variantId,
                    hasImages: hasImages
                };
                
                switchToVariant(variantData);
            }
        });
    });
    
    // Setup return to main product button
    if (returnToMainButton) {
        returnToMainButton.addEventListener('click', function() {
            switchToMainProduct();
        });
    }
    
    // Function to switch to a variant
    function switchToVariant(variantData) {
        // Store current variant ID
        currentVariantId = variantData.variantId;
        
        // 1. Update main display area with smooth transition
        fadeElementContent(modal.querySelector('.main-display-content'), function() {
            modal.querySelector('.main-display-name').textContent = variantData.name;
            modal.querySelector('.main-display-description').textContent = variantData.description || originalValues.description;
            modal.querySelector('.current-price').textContent = '$' + variantData.price.toFixed(2);
            
            // Update inventory display
            updateInventoryDisplay(variantData.inventoryCount);
            
            // Update image if provided
            if (variantData.imageUrl && mainImage) {
                mainImage.src = variantData.imageUrl;
            }
            
            // Update current variant name
            if (currentVariantName) {
                currentVariantName.textContent = variantData.name;
            }
        });
        
        // 2. Show the main product in variants list, hide the variant
        if (mainProductRow) mainProductRow.style.display = 'table-row';
        
        // Hide the selected variant row, show others
        variantRows.forEach(row => {
            if (row.dataset.itemId === 'variant-' + variantData.variantId) {
                row.style.display = 'none';
            } else {
                row.style.display = 'table-row';
            }
        });
        
        // 3. Update UI state indicators
        if (variantSpecificDetails) variantSpecificDetails.style.display = 'block';
        if (variantsSectionTitle) variantsSectionTitle.textContent = 'Other Available Options';
        
        // 4. Update form for adding to cart
        if (quantityInput) {
            quantityInput.value = 1;
            quantityInput.max = variantData.inventoryCount;
        }
        if (selectedVariantIdInput) selectedVariantIdInput.value = variantData.variantId;
        
        // Toggle add to cart button based on inventory
        if (addToCartBtn) addToCartBtn.disabled = variantData.inventoryCount <= 0;
        
        // 5. Update tracking state
        currentlyViewingMain = false;
        
        // 6. Update thumbnails to show variant images
        if (thumbnailsContainer) {
            // Show loading indicator in thumbnails container
            thumbnailsContainer.innerHTML = '<div class="d-flex justify-content-center align-items-center w-100 p-3"><i class="fas fa-spinner fa-spin me-2"></i> Loading images...</div>';
            
            // Get all unique variant images - this prevents duplicates
            const variantImages = getVariantImages(productId, variantData.variantId);
            
            // After getting variant images, update the thumbnails
            setTimeout(() => {
                // Clear loading indicator
                thumbnailsContainer.innerHTML = '';
                
                if (variantImages.length > 0) {
                    // Create thumbnails for each image
                    variantImages.forEach((image, index) => {
                        const thumbHtml = `
                            <div class="thumbnail-wrapper variant-thumb ${index === 0 ? 'active-thumb' : ''}" 
                                 data-image-url="${image.url}">
                                <img src="${image.url}" 
                                     alt="${image.alt || variantData.name + ' thumbnail ' + (index + 1)}"
                                     class="variant-thumbnail">
                            </div>
                        `;
                        thumbnailsContainer.innerHTML += thumbHtml;
                    });
                } else if (variantData.imageUrl) {
                    // Fall back to just the main variant image
                    const thumbHtml = `
                        <div class="thumbnail-wrapper variant-thumb active-thumb" 
                             data-image-url="${variantData.imageUrl}">
                            <img src="${variantData.imageUrl}" 
                                 alt="${variantData.name} thumbnail"
                                 class="variant-thumbnail">
                        </div>
                    `;
                    thumbnailsContainer.innerHTML = thumbHtml;
                } else {
                    // No images available
                    thumbnailsContainer.innerHTML = '<div class="d-flex justify-content-center align-items-center w-100 p-3 text-muted"><i class="fas fa-image me-2"></i> No images available</div>';
                }
                
                // Setup click handlers for new thumbnails
                setupThumbnailClickHandlers(thumbnailsContainer, mainImage);
            }, 300); // Short delay to ensure smooth transition
        }
    }
    
    // Function to get all images for a variant
    function getVariantImages(productId, variantId) {
        const images = [];
        const processedUrls = new Set(); // Keep track of processed URLs to avoid duplicates
        
        // Find the variant row in the DOM
        const variantRow = document.querySelector(`.variant-row[data-item-id="variant-${variantId}"]`);
        if (!variantRow) return images;
        
        // Get the variant object from the DOM data
        const variantButton = variantRow.querySelector('.select-item');
        if (!variantButton) return images;
        
        // First, add the main variant image
        if (variantButton.dataset.imageUrl) {
            images.push({
                id: 0,
                url: variantButton.dataset.imageUrl,
                alt: variantButton.dataset.name || 'Variant image'
            });
            processedUrls.add(variantButton.dataset.imageUrl);
        }
        
        // Find all images for this variant
        try {
            // Get the actual variant from the product
            const variant = findVariantById(productId, variantId);
            
            if (variant && variant.images && variant.images.length > 0) {
                // Add all variant images, skipping duplicates
                variant.images.forEach((image, index) => {
                    // Format the image URL correctly
                    const imageUrl = image.image_url.startsWith('http') 
                        ? image.image_url 
                        : '/storage/' + image.image_url;
                    
                    // Skip if this URL is already in our collection
                    if (processedUrls.has(imageUrl)) {
                        return;
                    }
                    
                    // Add the image and mark URL as processed
                    images.push({
                        id: image.id || index + 1,
                        url: imageUrl,
                        alt: variant.name + ' image ' + (index + 1)
                    });
                    processedUrls.add(imageUrl);
                });
            }
        } catch (e) {
            console.error('Error getting variant images:', e);
        }
        
        return images;
    }
    
    // Helper function to find variant by id in the product data
    function findVariantById(productId, variantId) {
        // Get the product element
        const productModal = document.getElementById('productModal' + productId);
        if (!productModal) return null;
        
        // Find the variant row
        const variantRow = productModal.querySelector(`.variant-row[data-item-id="variant-${variantId}"]`);
        if (!variantRow) return null;
        
        // Create a variant object with extracted attributes
        const variant = {
            id: variantId,
            name: '',
            images: []
        };
        
        // Get variant name from the row
        const nameCell = variantRow.querySelector('td:nth-child(2)');
        if (nameCell) {
            variant.name = nameCell.textContent.trim();
        }
        
        // Get images directly from the DOM
        // First check if variant has its own image
        const mainImageCell = variantRow.querySelector('td:nth-child(1) img.variant-thumbnail');
        if (mainImageCell) {
            variant.images.push({
                id: 0,
                image_url: mainImageCell.getAttribute('src')
            });
        }
        
        // If we're using a data attribute with serialized images, parse it
        const selectButton = variantRow.querySelector('.select-item');
        if (selectButton && selectButton.dataset.variantImages) {
            try {
                const additionalImages = JSON.parse(selectButton.dataset.variantImages);
                if (Array.isArray(additionalImages)) {
                    additionalImages.forEach(img => {
                        variant.images.push(img);
                    });
                }
            } catch (e) {
                console.error('Error parsing variant images JSON:', e);
            }
        }
        
        return variant;
    }
    
    // Function to switch back to main product
    function switchToMainProduct() {
        // Reset current variant ID
        currentVariantId = null;
        
        // 1. Update main display area with smooth transition
        fadeElementContent(modal.querySelector('.main-display-content'), function() {
            modal.querySelector('.main-display-name').textContent = originalValues.name;
            modal.querySelector('.main-display-description').textContent = originalValues.description;
            modal.querySelector('.current-price').textContent = '$' + originalValues.price.toFixed(2);
            
            // Update inventory display
            updateInventoryDisplay(originalValues.maxQuantity);
            
            // Update image back to original
            if (mainImage && originalValues.imageUrl) {
                mainImage.src = originalValues.imageUrl;
            }
        });
        
        // 2. Hide the main product in variants list, show all variants
        if (mainProductRow) mainProductRow.style.display = 'none';
        variantRows.forEach(row => {
            row.style.display = 'table-row';
        });
        
        // 3. Update UI state indicators
        if (variantSpecificDetails) variantSpecificDetails.style.display = 'none';
        if (variantsSectionTitle) variantsSectionTitle.textContent = 'Choose a Variant';
        
        // 4. Update form for adding to cart
        if (quantityInput) {
            quantityInput.value = 1;
            quantityInput.max = originalValues.maxQuantity;
        }
        if (selectedVariantIdInput) selectedVariantIdInput.value = '';
        
        // Toggle add to cart button based on inventory
        if (addToCartBtn) addToCartBtn.disabled = originalValues.maxQuantity <= 0;
        
        // 5. Update tracking state
        currentlyViewingMain = true;
        
        // 6. Restore original thumbnails
        if (thumbnailsContainer && thumbnailsContainer.dataset.originalHtml) {
            thumbnailsContainer.innerHTML = thumbnailsContainer.dataset.originalHtml;
            setupThumbnailClickHandlers(thumbnailsContainer, mainImage);
            
            // Reset active thumbnail
            const allThumbs = thumbnailsContainer.querySelectorAll('.thumbnail-wrapper');
            allThumbs.forEach(thumb => thumb.classList.remove('active-thumb'));
            if (allThumbs.length > 0) {
                allThumbs[0].classList.add('active-thumb');
            }
        }
    }
    
    // Helper function to set up click handlers for thumbnails
    function setupThumbnailClickHandlers(container, targetImage) {
    if (!container || !targetImage) return;
    
    const thumbnails = container.querySelectorAll('.thumbnail-wrapper');
    thumbnails.forEach(thumb => {
        // Remove existing event listeners to prevent duplicates
        const newThumb = thumb.cloneNode(true);
        thumb.parentNode.replaceChild(newThumb, thumb);
        
        // Add new event listener
        newThumb.addEventListener('click', function(e) {
            e.preventDefault();
            const imageUrl = this.dataset.imageUrl;
            
            if (imageUrl && targetImage) {
                targetImage.src = imageUrl;
                
                // Update active thumbnail - first remove active class from ALL current thumbnails
                // This is the key fix - get a fresh collection of all thumbnails at click time
                const allCurrentThumbnails = container.querySelectorAll('.thumbnail-wrapper');
                allCurrentThumbnails.forEach(t => t.classList.remove('active-thumb'));
                
                // Then add active class to the clicked thumbnail
                this.classList.add('active-thumb');
            }
        });
    });
}
    
    // Handle add to cart via AJAX
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Disable button and show loading
            addToCartBtn.disabled = true;
            const originalBtnText = addToCartBtn.innerHTML;
            addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Show success message
                addToCartBtn.innerHTML = '<i class="fas fa-check"></i> Added!';
                
                // Show notification
                const itemType = currentlyViewingMain ? 'Product' : 'Variant';
                showFloatingAlert(`${itemType} added to cart!`, 'success');
                
                // Update cart count in header if available
                if (data.cart_count && document.getElementById('cart-count')) {
                    document.getElementById('cart-count').textContent = data.cart_count;
                }
                
                // Reset button after delay
                setTimeout(() => {
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = originalBtnText;
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                showFloatingAlert('Failed to add to cart. Please try again.', 'danger');
                
                // Reset button
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = originalBtnText;
            });
        });
    }
    
    // Helper function to update inventory display
    function updateInventoryDisplay(count) {
        const stockDisplay = modal.querySelector('.main-display-stock');
        const inventoryCountElem = modal.querySelector('.inventory-count');
        
        if (!stockDisplay || !inventoryCountElem) return;
        
        // Clear existing status classes and icons
        const statusSpan = stockDisplay.querySelector('span:first-child');
        if (!statusSpan) return;
        
        statusSpan.className = '';
        
        // Update inventory count
        inventoryCountElem.textContent = count + ' left';
        
        // Set appropriate status class and icon
        if (count > 10) {
            statusSpan.className = 'badge bg-success';
            statusSpan.innerHTML = '<i class="fas fa-check-circle me-1"></i> In Stock';
        } else if (count > 0) {
            statusSpan.className = 'badge bg-warning text-dark';
            statusSpan.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Low Stock';
        } else {
            statusSpan.className = 'badge bg-danger';
            statusSpan.innerHTML = '<i class="fas fa-times-circle me-1"></i> Out of Stock';
        }
    }
}

// Helper function to fade element content
function fadeElementContent(element, callback) {
    if (!element) return;
    
    // Add fade-out class
    element.classList.add('fade-content');
    
    // Wait for fade to complete
    setTimeout(() => {
        // Execute callback to update content
        if (callback && typeof callback === 'function') {
            callback();
        }
        
        // Remove fade-out class
        setTimeout(() => {
            element.classList.remove('fade-content');
        }, 50);
    }, 300);
}

// Floating alert function
function showFloatingAlert(message, type = 'info') {
    // Create alert container if it doesn't exist
    let alertContainer = document.getElementById('floating-alert-container');
    
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.id = 'floating-alert-container';
        alertContainer.style.position = 'fixed';
        alertContainer.style.top = '20px';
        alertContainer.style.right = '20px';
        alertContainer.style.zIndex = '9999';
        document.body.appendChild(alertContainer);
    }
    
    // Create alert element
    const alertElement = document.createElement('div');
    alertElement.className = `alert alert-${type} alert-dismissible fade show`;
    alertElement.role = 'alert';
    alertElement.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to container
    alertContainer.appendChild(alertElement);
    
    // Auto dismiss after 3 seconds
    setTimeout(() => {
        alertElement.classList.remove('show');
        setTimeout(() => {
            alertElement.remove();
        }, 150);
    }, 3000);
}

// Helper function to format asset URLs
function asset(path) {
    // Check if path already starts with http or /
    if (path.startsWith('http') || path.startsWith('/')) {
        return path;
    }
    return '/storage/' + path;
}
</script>
@endsection