@extends('layouts.franchisee')

@section('title', 'Product Catalog - Franchisee Portal')

@section('page-title', 'Product Catalog')

@section('styles')
<style>
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
                           onchange="window.location.href='{{ route('franchisee.catalog', ['favorites' => request('favorites') ? 0 : 1]) }}'">
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
                                                <button type="submit" class="btn btn-sm btn-success rounded" {{ $product->stock_status == 'out_of_stock' ? 'disabled' : '' }} title="Add to Cart">
                                                    <i class="fas fa-cart-plus"></i>
                                                </button>
                                            </form>
                                            
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded product-favorite" data-product-id="{{ $product->id }}" title="Add to Favorites">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info rounded" data-bs-toggle="modal" data-bs-target="#productModal{{ $product->id }}" title="View Details">
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
            <div class="modal fade" id="productModal{{ $product->id }}" tabindex="-1" aria-labelledby="productModalLabel{{ $product->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalLabel{{ $product->id }}">{{ $product->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-5">
                                    @if($product->images && $product->images->count() > 0)
                                        <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                             class="img-fluid rounded" alt="{{ $product->name }}">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center" style="height: 250px;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-7">
                                    <h4>{{ $product->name }}</h4>
                                    <p class="text-muted">
                                        <span class="badge bg-secondary">{{ $product->category->name ?? 'Uncategorized' }}</span>
                                        <span class="ms-2"><i class="fas fa-building me-1"></i> {{ $product->supplier->name ?? 'Unknown Supplier' }}</span>
                                    </p>
                                    <p>{{ $product->description }}</p>
                                    
                                    <div class="mb-3">
                                        @if($product->inventory_count > 10)
                                            <span class="badge bg-success">In Stock</span>
                                        @elseif($product->inventory_count > 0)
                                            <span class="badge bg-warning text-dark">Low Stock</span>
                                        @else
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @endif
                                        
                                        <span class="ms-2">{{ $product->inventory_count ?? 0 }} left</span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>Product Details:</strong></p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-weight me-2"></i> {{ $product->unit_size ?? '' }} {{ $product->unit_type ?? '' }}</li>
                                            <li><i class="fas fa-box me-2"></i> SKU: {{ $product->sku ?? 'N/A' }}</li>
                                            @if(isset($product->min_order_quantity) && $product->min_order_quantity > 0)
                                                <li><i class="fas fa-truck-loading me-2"></i> Min Order: {{ $product->min_order_quantity }}</li>
                                            @endif
                                        </ul>
                                    </div>
                                    
                                    <div class="d-flex align-items-center mb-3">
                                        <h3 class="me-3">${{ number_format($product->base_price, 2) }}</h3>
                                        @if(isset($product->compare_price) && $product->compare_price > $product->base_price)
                                            <span class="text-muted text-decoration-line-through">${{ number_format($product->compare_price, 2) }}</span>
                                            <span class="badge bg-danger ms-2">{{ round((1 - $product->base_price / $product->compare_price) * 100) }}% OFF</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <form action="{{ route('franchisee.cart.add') }}" method="POST" class="d-flex align-items-center w-100">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                
                                <div class="d-flex align-items-center me-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary quantity-decrement">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->inventory_count }}" class="form-control mx-2" style="width: 60px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary quantity-increment">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                
                                <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">Close</button>
                                
                                <button type="button" class="btn btn-outline-secondary me-2 product-favorite" data-product-id="{{ $product->id }}">
                                    <i class="fas fa-heart"></i> 
                                    Add to Favorites
                                </button>
                                
                                <button type="submit" class="btn btn-success" {{ $product->inventory_count <= 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                                </button>
                            </form>
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
        // Get all favorites from local storage or initialize empty array
        let favorites = JSON.parse(localStorage.getItem('product_favorites')) || [];
        
        // Handle the "Show favorites only" checkbox
        const showFavoritesCheckbox = document.getElementById('show_favorites');
        
        if (showFavoritesCheckbox) {
            // Set initial checkbox state based on URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const showOnlyFavorites = urlParams.get('favorites') === '1';
            showFavoritesCheckbox.checked = showOnlyFavorites;
            
            // Filter products based on favorites selection
            if (showOnlyFavorites) {
                filterProductsByFavorites();
            }
            
            // Handle checkbox change event
            showFavoritesCheckbox.addEventListener('change', function() {
                const urlParams = new URLSearchParams(window.location.search);
                
                if (this.checked) {
                    urlParams.set('favorites', '1');
                    filterProductsByFavorites();
                } else {
                    urlParams.delete('favorites');
                    // Show all products again
                    document.querySelectorAll('tr.product-row').forEach(row => {
                        row.style.display = '';
                    });
                }
                
                // Update URL without refreshing the page
                window.history.replaceState({}, '', `${window.location.pathname}?${urlParams}`);
            });
        }
        
        // Function to filter products by favorites
        function filterProductsByFavorites() {
            document.querySelectorAll('tr.product-row').forEach(row => {
                const productId = row.dataset.productId;
                if (!favorites.includes(productId)) {
                    row.style.display = 'none';
                } else {
                    row.style.display = '';
                }
            });
        }
        
        // Apply favorite states to heart icons when page loads
        favorites.forEach(productId => {
            const icons = document.querySelectorAll('[data-product-id="' + productId + '"] .fa-heart');
            icons.forEach(icon => {
                icon.classList.add('text-danger');
            });
        });
        
        const favoriteButtons = document.querySelectorAll('.product-favorite');
        
        favoriteButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                const productId = this.dataset.productId;
                // Select all heart icons for this product ID, regardless of parent element class
                const icons = document.querySelectorAll('[data-product-id="' + productId + '"] .fa-heart');
                
                // Toggle favorite state
                const isFavorite = favorites.includes(productId);
                
                if (isFavorite) {
                    // Remove from favorites
                    favorites = favorites.filter(id => id !== productId);
                    // Update UI
                    icons.forEach(icon => {
                        icon.classList.remove('text-danger');
                    });
                    showFloatingAlert('Product removed from favorites.', 'info');
                    
                    // If showing favorites only, hide this product
                    if (showFavoritesCheckbox && showFavoritesCheckbox.checked) {
                        const productRow = document.querySelector('tr.product-row[data-product-id="' + productId + '"]');
                        if (productRow) {
                            productRow.style.display = 'none';
                        }
                    }
                } else {
                    // Add to favorites
                    favorites.push(productId);
                    // Update UI
                    icons.forEach(icon => {
                        icon.classList.add('text-danger');
                    });
                    showFloatingAlert('Product added to favorites!', 'success');
                }
                
                // Save to local storage
                localStorage.setItem('product_favorites', JSON.stringify(favorites));
            });
        });
        
        // Handle quantity selectors
        const decrementButtons = document.querySelectorAll('.quantity-decrement');
        const incrementButtons = document.querySelectorAll('.quantity-increment');
        
        decrementButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.nextElementSibling;
                let value = parseInt(input.value);
                if (value > parseInt(input.min)) {
                    input.value = value - 1;
                }
            });
        });
        
        incrementButtons.forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                let value = parseInt(input.value);
                if (value < parseInt(input.max) || !input.max) {
                    input.value = value + 1;
                }
            });
        });
    });
</script>
@endsection