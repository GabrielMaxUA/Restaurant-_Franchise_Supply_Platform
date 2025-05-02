@extends('layouts.admin')

@section('title', 'Add New Product - Restaurant Franchise Supply Platform')

@section('page-title', 'Add New Product')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" id="product-form">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name') }}" required
                            placeholder="Enter product name">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback custom-invalid-feedback" id="name-error">
                            Product name is required
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category_id">Category <span class="text-danger">*</span></label>
                        <select class="form-control @error('category_id') is-invalid @enderror" 
                            id="category_id" name="category_id" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" 
                    id="description" name="description" rows="4"
                    placeholder="Enter product description">{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="base_price">Base Price ($) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" min="0" 
                                class="form-control price-input @error('base_price') is-invalid @enderror" 
                                id="base_price" name="base_price" 
                                value="{{ old('base_price') }}" required
                                placeholder="0.00">
                        </div>
                        @error('base_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback custom-invalid-feedback" id="price-error">
                            Valid price is required
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="inventory_count">Inventory Count <span class="text-danger">*</span></label>
                        <input type="number" min="0" 
                            class="form-control @error('inventory_count') is-invalid @enderror" 
                            id="inventory_count" name="inventory_count" 
                            value="{{ old('inventory_count') }}" required
                            placeholder="Enter quantity">
                        @error('inventory_count')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="invalid-feedback custom-invalid-feedback" id="inventory-error">
                            Inventory count is required
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group mb-3">
                <label for="images">Product Images</label>
                <input type="file" class="form-control @error('images') is-invalid @enderror" 
                    id="images" name="images[]" multiple accept="image/*">
                @error('images')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">You can select multiple images. Supported formats: JPG, PNG, GIF</small>
                <div id="product-image-preview" class="mt-2 row"></div>
            </div>
            
            <h4 class="mt-4">Product Variants</h4>
            <div id="variants-container">
                <div class="card mb-3 variant-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Variant Name</label>
                                    <input type="text" class="form-control" name="variants[0][name]" 
                                        placeholder="e.g., Size, Color, Package">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Price Adjustment ($)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control price-input" 
                                            name="variants[0][price_adjustment]" value="0.00"
                                            placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Inventory Count</label>
                                    <input type="number" min="0" class="form-control" 
                                        name="variants[0][inventory_count]" value="0"
                                        placeholder="Enter quantity">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Variant Image Upload -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Variant Image (Optional)</label>
                                    <input type="file" class="form-control variant-image"
                                      name="variant_image_0" accept="image/*">

                                    <small class="form-text text-muted">Upload an image specific to this variant (e.g., different color).</small>
                                    <div class="variant-image-preview mt-2 row"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <button type="button" id="add-variant" class="btn btn-outline-secondary">
                    <i class="fas fa-plus me-2"></i>Add Another Variant
                </button>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="submit-button">Create Product</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // JavaScript to handle dynamic variant addition and image previews
    document.addEventListener('DOMContentLoaded', function() {
        let variantIndex = 0;
        
        // Image preview for product images
        document.getElementById('images').addEventListener('change', function(event) {
            const previewContainer = document.getElementById('product-image-preview');
            previewContainer.innerHTML = ''; // Clear previous previews
            
            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-2 mb-2';
                    
                    const card = document.createElement('div');
                    card.className = 'card h-100';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'card-img-top';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    img.alt = 'Product Image Preview';
                    
                    card.appendChild(img);
                    col.appendChild(card);
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        });
        
        // Function to set up variant image preview
        function setupVariantImagePreview(variantImageInput, previewContainer) {
            variantImageInput.addEventListener('change', function(event) {
                previewContainer.innerHTML = ''; // Clear previous preview
                
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-3 mb-2';
                        
                        const card = document.createElement('div');
                        card.className = 'card';
                        
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'card-img-top';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        img.alt = 'Variant Image Preview';
                        
                        card.appendChild(img);
                        col.appendChild(card);
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
        
        // Setup initial variant image preview
        const initialVariantImage = document.querySelector('.variant-image');
        const initialPreviewContainer = document.querySelector('.variant-image-preview');
        setupVariantImagePreview(initialVariantImage, initialPreviewContainer);
        
        // Handle placeholder removal and add validation classes on focus
        const priceInputs = document.querySelectorAll('.price-input');
        priceInputs.forEach(input => {
            // Clear default value when focused
            input.addEventListener('focus', function() {
                if (this.value === '0.00') {
                    this.value = '';
                }
            });
            
            // Restore default if left empty
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = '0.00';
                }
            });
        });
        
        // Form validation before submit
        document.getElementById('product-form').addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate product name
            const nameInput = document.getElementById('name');
            if (!nameInput.value.trim()) {
                nameInput.classList.add('is-invalid');
                document.getElementById('name-error').style.display = 'block';
                isValid = false;
            } else {
                nameInput.classList.remove('is-invalid');
                document.getElementById('name-error').style.display = 'none';
            }
            
            // Validate price
            const priceInput = document.getElementById('base_price');
            if (!priceInput.value || parseFloat(priceInput.value) < 0) {
                priceInput.classList.add('is-invalid');
                document.getElementById('price-error').style.display = 'block';
                isValid = false;
            } else {
                priceInput.classList.remove('is-invalid');
                document.getElementById('price-error').style.display = 'none';
            }
            
            // Validate inventory
            const inventoryInput = document.getElementById('inventory_count');
            if (!inventoryInput.value || parseInt(inventoryInput.value) < 0) {
                inventoryInput.classList.add('is-invalid');
                document.getElementById('inventory-error').style.display = 'block';
                isValid = false;
            } else {
                inventoryInput.classList.remove('is-invalid');
                document.getElementById('inventory-error').style.display = 'none';
            }
            
            if (!isValid) {
                e.preventDefault();
                // Scroll to the first error
                const firstError = document.querySelector('.is-invalid');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
        
        document.getElementById('add-variant').addEventListener('click', function() {
            variantIndex++;
            
            const variantTemplate = `
    <div class="card mb-3 variant-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Variant Name</label>
                        <input type="text" class="form-control" name="variants[${variantIndex}][name]" 
                            placeholder="e.g., Size, Color, Package">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Price Adjustment ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control price-input" 
                                name="variants[${variantIndex}][price_adjustment]" value="0.00"
                                placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Inventory Count</label>
                        <input type="number" min="0" class="form-control" 
                            name="variants[${variantIndex}][inventory_count]" value="0"
                            placeholder="Enter quantity">
                    </div>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <div class="form-group">
                        <label>Variant Image (Optional)</label>
                        <input type="file" class="form-control variant-image" 
                            name="variant_image_${variantIndex}" accept="image/*">
                        <small class="form-text text-muted">Upload an image specific to this variant (e.g., different color).</small>
                        <div class="variant-image-preview mt-2 row"></div>
                    </div>
                </div>
            </div>
            
            <button type="button" class="btn btn-sm btn-danger mt-2 remove-variant">
                <i class="fas fa-trash me-2"></i>Remove Variant
            </button>
        </div>
    </div>
`;

            
            document.getElementById('variants-container').insertAdjacentHTML('beforeend', variantTemplate);
            
            // Setup image preview for the new variant
            const newVariantImage = document.querySelector(`.variant-card:last-child .variant-image`);
            const newPreviewContainer = document.querySelector(`.variant-card:last-child .variant-image-preview`);
            setupVariantImagePreview(newVariantImage, newPreviewContainer);
            
            // Add event listeners to new remove buttons
            document.querySelectorAll('.remove-variant').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.variant-card').remove();
                });
            });
            
            // Add event listeners to new price inputs
            const newPriceInputs = document.querySelectorAll(`.variant-card:last-child .price-input`);
            newPriceInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    if (this.value === '0.00') {
                        this.value = '';
                    }
                });
                
                input.addEventListener('blur', function() {
                    if (this.value === '') {
                        this.value = '0.00';
                    }
                });
            });
        });
    });
</script>

<style>
    .custom-invalid-feedback {
        display: none;
        width: 100%;
        margin-top: 0.25rem;
        font-size: 0.875em;
        color: #dc3545;
    }
    
    .price-input:focus {
        box-shadow: none;
        border-color: #86b7fe;
    }
    
    .variant-image-preview img, 
    #product-image-preview img {
        object-fit: cover;
        border-radius: 4px;
    }
</style>
@endsection