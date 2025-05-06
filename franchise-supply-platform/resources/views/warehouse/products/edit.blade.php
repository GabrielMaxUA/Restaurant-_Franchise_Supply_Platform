@extends('layouts.warehouse')

@section('title', 'Edit Product - Restaurant Franchise Supply Platform')

@section('page-title', 'Edit Product')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Product</h1>
        <a href="{{ route('warehouse.products.index') }}" class="btn btn-secondary">Back to Products</a>
    </div>
    
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <form action="{{ route('warehouse.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="card mb-4">
            <div class="card-header">Basic Information</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="base_price" class="form-label">Base Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="base_price" name="base_price" 
                                   value="{{ old('base_price', $product->base_price) }}" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="inventory_count" class="form-label">Inventory Count</label>
                        <input type="number" class="form-control" id="inventory_count" name="inventory_count" 
                               value="{{ old('inventory_count', $product->inventory_count) }}" min="0" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Product Images - Moved before variants -->
        <div class="card mb-4">
            <div class="card-header">Product Images</div>
            <div class="card-body">
                <div class="mb-4">
                    <label for="images" class="form-label">Upload New Images</label>
                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                    <small class="text-muted">You can select multiple files. Supported formats: JPG, PNG, GIF (max 2MB each)</small>
                    <div id="product-image-previews" class="row mt-3"></div>
                </div>
                
                @if($product->images->count() > 0)
                    <div class="mt-4">
                        <h5>Current Images</h5>
                        <div class="row">
                            @foreach($product->images as $image)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <img src="{{ asset('storage/' . $image->image_url) }}" class="card-img-top" 
                                             style="height: 150px; object-fit: contain;" alt="Product Image">
                                        <div class="card-body p-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="delete_images[]" 
                                                      value="{{ $image->id }}" id="delete-image-{{ $image->id }}">
                                                <label class="form-check-label" for="delete-image-{{ $image->id }}">
                                                    Delete
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Existing Variants -->
        @if($product->variants->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">Existing Variants</div>
            <div class="card-body">
                @foreach($product->variants as $variant)
                <div class="border rounded p-3 mb-3">
                    <div class="row align-items-center mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Variant Name</label>
                            <input type="text" class="form-control" name="existing_variants[{{ $variant->id }}][name]" 
                                   value="{{ old('existing_variants.'.$variant->id.'.name', $variant->name) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Price Adjustment</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" 
                                       name="existing_variants[{{ $variant->id }}][price_adjustment]" 
                                       value="{{ old('existing_variants.'.$variant->id.'.price_adjustment', $variant->price_adjustment) }}" 
                                       step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Inventory</label>
                            <input type="number" class="form-control" 
                                   name="existing_variants[{{ $variant->id }}][inventory_count]" 
                                   value="{{ old('existing_variants.'.$variant->id.'.inventory_count', $variant->inventory_count) }}" 
                                   min="0">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="existing_variants[{{ $variant->id }}][delete]" 
                                       id="delete-variant-{{ $variant->id }}" value="1">
                                <label class="form-check-label text-danger" for="delete-variant-{{ $variant->id }}">
                                    Delete this variant
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Variant Image Section -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Variant Image</label>
                            <input type="file" class="form-control variant-image" 
                                   name="variant_image_existing_{{ $variant->id }}[]" multiple accept="image/*">
                            <small class="text-muted">Upload new images for this variant</small>
                            <div class="variant-image-preview mt-2 row"></div>
                        </div>
                        
                        <div class="col-md-6">
                            @if($variant->images && $variant->images->count() > 0)
                                <div class="variant-image-container">
                                    <label class="form-label">Current Images ({{ $variant->images->count() }})</label>
                                    <div class="row">
                                        @foreach($variant->images as $variantImage)
                                            <div class="col-md-6 mb-2">
                                                <div class="card">
                                                    <img src="{{ asset('storage/' . $variantImage->image_url) }}" 
                                                         class="card-img-top" style="height: 100px; object-fit: contain;" alt="Variant Image">
                                                    <div class="card-body p-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" 
                                                                   name="delete_variant_images[{{ $variantImage->id }}]" 
                                                                   id="delete-variant-image-{{ $variantImage->id }}" value="1">
                                                            <label class="form-check-label" for="delete-variant-image-{{ $variantImage->id }}">
                                                                Delete
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">No images for this variant</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- New Variants -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Add New Variants</span>
                <button type="button" class="btn btn-sm btn-primary" id="add-variant">Add Variant</button>
            </div>
            <div class="card-body">
                <div id="new-variants-container">
                    @if(old('new_variants'))
                        @foreach(old('new_variants') as $index => $variant)
                            <div class="new-variant-row border rounded p-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Variant Name</label>
                                        <input type="text" class="form-control" name="new_variants[{{ $index }}][name]" value="{{ $variant['name'] }}" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Price Adjustment</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" name="new_variants[{{ $index }}][price_adjustment]" 
                                                   value="{{ $variant['price_adjustment'] ?? 0 }}" step="0.01">
                                        </div>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="form-label">Inventory</label>
                                        <input type="number" class="form-control" name="new_variants[{{ $index }}][inventory_count]" 
                                               value="{{ $variant['inventory_count'] ?? 0 }}" min="0">
                                    </div>
                                    <div class="col-md-2 mb-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger remove-variant">Remove</button>
                                    </div>
                                </div>
                                
                                <!-- New Variant Image Upload -->
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <label class="form-label">Variant Images</label>
                                        <input type="file" class="form-control new-variant-image" 
                                               name="variant_image_new_{{ $index }}[]" multiple accept="image/*">
                                        <small class="text-muted">Upload images for this variant</small>
                                        <div class="new-variant-image-preview mt-2 row"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <p id="no-variants-message" class="{{ old('new_variants') ? 'd-none' : '' }}">No new variants added yet.</p>
            </div>
        </div>
        
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary">Update Product</button>
            <a href="{{ route('warehouse.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const newVariantsContainer = document.getElementById('new-variants-container');
    const noVariantsMessage = document.getElementById('no-variants-message');
    const addVariantButton = document.getElementById('add-variant');
    let variantIndex = {{ count(old('new_variants', [])) }};
    
    // Add new variant
    addVariantButton.addEventListener('click', function() {
        noVariantsMessage.classList.add('d-none');
        
        const variantHtml = `
            <div class="new-variant-row border rounded p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Variant Name</label>
                        <input type="text" class="form-control" name="new_variants[${variantIndex}][name]" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Price Adjustment</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="new_variants[${variantIndex}][price_adjustment]" value="0" step="0.01">
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Inventory</label>
                        <input type="number" class="form-control" name="new_variants[${variantIndex}][inventory_count]" value="0" min="0">
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger remove-variant">Remove</button>
                    </div>
                </div>
                
                <!-- New Variant Image Upload -->
                <div class="row mt-2">
                    <div class="col-12">
                        <label class="form-label">Variant Images</label>
                        <input type="file" class="form-control new-variant-image" 
                               name="variant_image_new_${variantIndex}[]" multiple accept="image/*">
                        <small class="text-muted">Upload images for this variant</small>
                        <div class="new-variant-image-preview mt-2 row"></div>
                    </div>
                </div>
            </div>
        `;
        
        newVariantsContainer.insertAdjacentHTML('beforeend', variantHtml);
        
        // Setup image preview for the new variant
        const newVariantImage = document.querySelector(`.new-variant-row:last-child .new-variant-image`);
        const newPreviewContainer = document.querySelector(`.new-variant-row:last-child .new-variant-image-preview`);
        setupVariantImagePreview(newVariantImage, newPreviewContainer);
        
        variantIndex++;
    });
    
    // Remove variant
    newVariantsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            e.target.closest('.new-variant-row').remove();
            
            // Show message if no variants
            if (newVariantsContainer.querySelectorAll('.new-variant-row').length === 0) {
                noVariantsMessage.classList.remove('d-none');
            }
        }
    });
    
    // Function to set up variant image preview with multiple file support
    function setupVariantImagePreview(variantImageInput, previewContainer) {
        variantImageInput.addEventListener('change', function(event) {
            previewContainer.innerHTML = ''; // Clear previous preview
            
            if (this.files && this.files.length > 0) {
                const row = document.createElement('div');
                row.className = 'row';
                previewContainer.appendChild(row);
                
                Array.from(this.files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-4 mb-2';
                        
                        const card = document.createElement('div');
                        card.className = 'card';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'card-img-top';
                        img.style.height = '150px';
                        img.style.objectFit = 'contain';
                        img.alt = 'Variant Image Preview';
                        
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body p-2';
                        cardBody.innerHTML = `<small class="text-muted">${file.name}</small>`;
                        
                        card.appendChild(img);
                        card.appendChild(cardBody);
                        col.appendChild(card);
                        row.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }
    
    // Setup image previews for any existing variant image inputs
    document.querySelectorAll('.new-variant-image').forEach(function(input) {
        const previewContainer = input.nextElementSibling.nextElementSibling;
        setupVariantImagePreview(input, previewContainer);
    });
    
    // Setup image previews for existing variant image inputs
    document.querySelectorAll('.variant-image').forEach(function(input) {
        const previewContainer = input.nextElementSibling.nextElementSibling;
        setupVariantImagePreview(input, previewContainer);
    });
    
    // Product image preview
    document.getElementById('images').addEventListener('change', function(event) {
        const previewContainer = document.getElementById('product-image-previews');
        previewContainer.innerHTML = ''; // Clear previous previews
        
        if (this.files && this.files.length > 0) {
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 mb-3';
                    
                    const card = document.createElement('div');
                    card.className = 'card';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'card-img-top';
                    img.style.height = '150px';
                    img.style.objectFit = 'contain';
                    img.alt = 'Image Preview';
                    
                    const cardBody = document.createElement('div');
                    cardBody.className = 'card-body p-2';
                    cardBody.innerHTML = `<small class="text-muted">${file.name}</small>`;
                    
                    card.appendChild(img);
                    card.appendChild(cardBody);
                    col.appendChild(card);
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }
    });
});
</script>
@endpush
@endsection