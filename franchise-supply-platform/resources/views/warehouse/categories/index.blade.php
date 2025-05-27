@extends('layouts.warehouse')
@section('title', 'Categories - Restaurant Franchise Supply Platform')
@section('page-title', 'Categories')

@section('styles')
<style>
    .bulk-select-checkbox {
        cursor: pointer;
    }
    .bulk-actions {
        display: none;
        margin-bottom: 1rem;
    }
    .bulk-actions.show {
        display: flex;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Category Management</h1>
    <a href="{{ route('warehouse.categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Category
    </a>
</div>

<!-- Filter Section -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('warehouse.categories.index') }}" class="filters-form">
            <div class="row align-items-end">
                <!-- Search -->
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">Search Categories</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name or description..." 
                               value="{{ request('search') }}">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                    </div>
                </div>
                
                <!-- Sort By -->
                <div class="col-md-3 mb-3">
                    <label for="sort_by" class="form-label">Sort By</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="products_count" {{ request('sort_by') == 'products_count' ? 'selected' : '' }}>Products Count</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date Created</option>
                    </select>
                </div>
                
                <!-- Sort Order -->
                <div class="col-md-3 mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <select class="form-select" id="sort_order" name="sort_order">
                        <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Ascending</option>
                        <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Descending</option>
                    </select>
                </div>
                
                <!-- Filter Actions -->
                <div class="col-md-2 mb-3">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('warehouse.categories.index') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<!-- Bulk Actions -->
<div class="bulk-actions alert alert-info d-flex justify-content-between align-items-center" id="bulkActions">
    <div>
        <span class="selected-count">0</span> item(s) selected
    </div>
    <button type="button" class="btn btn-danger" onclick="bulkDelete()">
        <i class="fas fa-trash me-2"></i>Delete Selected
    </button>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>
                            Name
                            @if(request('sort_by') == 'name')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @endif
                        </th>
                        <th>Description</th>
                        <th>
                            Products Count
                            @if(request('sort_by') == 'products_count')
                                <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @endif
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input bulk-select-checkbox" value="{{ $category->id }}">
                            </td>
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
                            <td colspan="6" class="text-center py-4">
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
        
        <!-- Pagination -->
        @include('components.pagination', ['items' => $categories])
    </div>
</div>
@endsection

@section('scripts')
<script>
    let selectedIds = [];
    
    // Select/Deselect all checkboxes
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.bulk-select-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActions();
    });
    
    // Individual checkbox change
    document.querySelectorAll('.bulk-select-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        selectedIds = [];
        const checkboxes = document.querySelectorAll('.bulk-select-checkbox:checked');
        checkboxes.forEach(checkbox => {
            selectedIds.push(checkbox.value);
        });
        
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = bulkActions.querySelector('.selected-count');
        
        if (selectedIds.length > 0) {
            bulkActions.classList.add('show');
            selectedCount.textContent = selectedIds.length;
        } else {
            bulkActions.classList.remove('show');
        }
        
        // Update select all checkbox state
        const selectAll = document.getElementById('selectAll');
        const totalCheckboxes = document.querySelectorAll('.bulk-select-checkbox').length;
        selectAll.checked = selectedIds.length === totalCheckboxes && totalCheckboxes > 0;
    }
    
    function bulkDelete() {
        if (selectedIds.length === 0) return;
        
        if (confirm(`Are you sure you want to delete ${selectedIds.length} selected categories?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('warehouse.categories.bulk-delete') }}';
            
            // Add CSRF token
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add method override for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            
            // Add selected IDs
            selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'category_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection