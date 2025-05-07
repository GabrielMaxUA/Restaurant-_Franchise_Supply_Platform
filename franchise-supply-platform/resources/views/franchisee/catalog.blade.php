@extends('layouts.franchisee')

@section('title', 'Product Catalog - Franchisee Portal')

@section('page-title', 'Product Catalog')

@section('styles')
<style>
/* Basic styling needed for the catalog page itself */
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

.status-variants-only {
    color: #ffc107;
    background-color: rgba(255, 193, 7, 0.1);
    padding: 2px 8px;
    border-radius: 4px;
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
    @elseif($product->has_in_stock_variants)
        <span class="status-variants-only"><i class="fas fa-exclamation-circle me-1"></i> Variants Only</span>
    @else
        <span class="status-out-of-stock"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
    @endif
</td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-between align-items-center">
                                        <button type="button" class="btn btn-sm btn-success rounded quick-add-to-cart" 
                                          data-product-id="{{ $product->id }}"
                                          {{ $product->inventory_count <= 0 ? 'disabled' : '' }} 
                                          title="Add to Cart">
                                          <i class="fas fa-cart-plus"></i>
                                       </button>
                                            
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-secondary rounded product-favorite" data-product-id="{{ $product->id }}" title="{{ $product->is_favorite > 0 ? 'Remove from Favorites' : 'Add to Favorites' }}">
                                                    <i class="fas fa-heart {{ $product->is_favorite > 0 ? 'text-danger' : '' }}"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info rounded view-product-btn quick-add-to-cart" 
                                                    data-product-id="{{ $product->id }}" 
                                                    title="View Details">
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
        @endif
    </div>
</div>

@endsection

@section('scripts')
@include('layouts.components.alert-component')
@include('layouts.components.add-to-cart')

<script>
// Toggle advanced filters
document.getElementById('toggleFilters').addEventListener('click', function() {
    const filtersSection = document.getElementById('advancedFilters');
    filtersSection.style.display = filtersSection.style.display === 'none' ? 'block' : 'none';
});

document.addEventListener('DOMContentLoaded', function() {
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
</script>
@endsection