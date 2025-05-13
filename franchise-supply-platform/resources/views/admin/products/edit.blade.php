@extends('layouts.admin')

@section('title', 'Edit Product - Restaurant Franchise Supply Platform')

@section('page-title', 'Edit Product')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Product</h1>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Back to Products</a>
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
    
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
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
                <!-- File Upload Guidelines Box -->
                <div class="alert alert-info mb-3">
                    <h5><i class="fas fa-info-circle"></i> File Upload Guidelines</h5>
                    <ul>
                        <li>You can upload <strong>multiple images</strong> at once by holding Ctrl (or Cmd on Mac) while selecting files</li>
                        <li>Each image must be under 2MB in size</li>
                        <li>Supported formats: JPG, PNG, GIF</li>
                        <li>Maximum 5 images per product</li>
                    </ul>
                </div>
                
                <div class="mb-4">
                    <label for="images" class="form-label">Upload New Images</label>
                    <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                    <small class="text-muted">You can select multiple files by holding Ctrl (or Cmd on Mac) while selecting. Supported formats: JPG, PNG, GIF (max 2MB each)</small>
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
                            <label class="form-label">Price</label>
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
                                        <label class="form-label">Price</label>
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
                                    <div class="col-md-2 mt-3 d-flex align-items-end">
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
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM fully loaded");
    
    // Set up file validation constants
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    const MAX_FILES = 5; // Maximum 5 files per input
    
    // Function to clear all size warnings
    function clearSizeWarnings() {
        document.querySelectorAll('.alert-warning').forEach(warning => {
            warning.remove();
        });
    }
    
    // Function to validate file size and display warnings
    function validateFileSize(file, container) {
        if (file.size > MAX_FILE_SIZE) {
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            
            // Show a flash warning message
            const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                <strong>Warning!</strong> File "${file.name}" (${fileSizeMB}MB) exceeds the 2MB size limit and will be rejected.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            const warningContainer = document.createElement('div');
            warningContainer.innerHTML = warningHTML;
            container.appendChild(warningContainer.firstChild);
            return false;
        }
        return true;
    }
    
    const newVariantsContainer = document.getElementById('new-variants-container');
    const noVariantsMessage = document.getElementById('no-variants-message');
    const addVariantButton = document.getElementById('add-variant');
    
    console.log("newVariantsContainer:", newVariantsContainer);
    console.log("noVariantsMessage:", noVariantsMessage);
    console.log("addVariantButton:", addVariantButton);
    
    // Also try to get the button by class name or text content as fallback
    if (!addVariantButton) {
        console.log("Trying to find button by class or text content");
        const buttons = document.querySelectorAll("button");
        buttons.forEach(button => {
            console.log("Found button:", button.textContent, button);
            if (button.textContent.trim() === "Add Variant") {
                console.log("Found button with 'Add Variant' text");
                addVariantButton = button;
            }
        });
    }
    
    let variantIndex = 0; // Default to 0 in case the Blade syntax doesn't render
    console.log("Initial variantIndex:", variantIndex);
    
    // Add new variant
    if (addVariantButton) {
        console.log("Setting up click listener for add variant button");
        addVariantButton.addEventListener('click', function(e) {
            console.log("Add variant button clicked!");
            e.preventDefault(); // Prevent any default form submission
            
            if (noVariantsMessage) {
                noVariantsMessage.classList.add('d-none');
            } else {
                console.log("noVariantsMessage element not found");
            }
            
            const variantHtml = `
                <div class="new-variant-row border rounded p-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-4 mb-2">
                            <label class="form-label">Variant Name</label>
                            <input type="text" class="form-control" name="new_variants[${variantIndex}][name]" required>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" name="new_variants[${variantIndex}][price_adjustment]" value="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label">Inventory</label>
                            <input type="number" class="form-control" name="new_variants[${variantIndex}][inventory_count]" value="0" min="0">
                        </div>
                        <div class="col-md-2 d-flex align-items-end" style="margin-top: 1.5rem;">
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
            
            if (newVariantsContainer) {
                console.log("Inserting new variant HTML");
                newVariantsContainer.insertAdjacentHTML('beforeend', variantHtml);
                
                // Setup image preview for the new variant
                const newVariantImage = document.querySelector(`.new-variant-row:last-child .new-variant-image`);
                const newPreviewContainer = document.querySelector(`.new-variant-row:last-child .new-variant-image-preview`);
                
                console.log("New variant image element:", newVariantImage);
                console.log("New variant preview container:", newPreviewContainer);
                
                if (newVariantImage && newPreviewContainer) {
                    setupVariantImagePreview(newVariantImage, newPreviewContainer);
                } else {
                    console.log("Could not find new variant image elements");
                }
                
                variantIndex++;
                console.log("Incremented variantIndex to:", variantIndex);
            } else {
                console.log("newVariantsContainer is null, cannot add variant");
            }
        });
    } else {
        console.log("Add variant button not found in the DOM even after trying alternatives");
    }
    
    // Remove variant
    if (newVariantsContainer) {
        console.log("Setting up click listener for remove variant buttons");
        newVariantsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-variant')) {
                console.log("Remove variant button clicked");
                e.target.closest('.new-variant-row').remove();
                
                // Show message if no variants
                if (newVariantsContainer.querySelectorAll('.new-variant-row').length === 0 && noVariantsMessage) {
                    noVariantsMessage.classList.remove('d-none');
                }
            }
        });
    }
    
    // Function to set up variant image preview with multiple file support
    function setupVariantImagePreview(variantImageInput, previewContainer) {
        console.log("Setting up image preview for:", variantImageInput);
        variantImageInput.addEventListener('change', function(event) {
            console.log("Variant image input changed");
            previewContainer.innerHTML = ''; // Clear previous preview
            
            // Find parent container for warnings
            const parentSection = variantImageInput.closest('.file-section') || variantImageInput.closest('.form-group');
            clearSizeWarnings(); // Clear any existing warnings
            
            if (this.files && this.files.length > 0) {
                console.log("Files selected:", this.files.length);
                
                // Check if too many files are selected
                if (this.files.length > MAX_FILES) {
                    const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                        <strong>Warning!</strong> You selected ${this.files.length} files for this variant. Maximum ${MAX_FILES} files are allowed per variant.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    const warningContainer = document.createElement('div');
                    warningContainer.innerHTML = warningHTML;
                    parentSection.appendChild(warningContainer.firstChild);
                }
                
                const row = document.createElement('div');
                row.className = 'row';
                previewContainer.appendChild(row);
                
                Array.from(this.files).forEach(file => {
                    // Validate file size
                    validateFileSize(file, parentSection);
                    
                    // Check file type
                    if (!file.type.match('image/(jpeg|png|gif|jpg)')) {
                        const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                            <strong>Warning!</strong> File "${file.name}" is not a supported format (JPG, PNG, GIF).
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                        const warningContainer = document.createElement('div');
                        warningContainer.innerHTML = warningHTML;
                        parentSection.appendChild(warningContainer.firstChild);
                    }
                    
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
                        
                        console.log("Added preview for:", file.name);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    }
    
    // Setup image previews for any existing variant image inputs
    const newVariantImages = document.querySelectorAll('.new-variant-image');
    console.log("Found new variant image inputs:", newVariantImages.length);
    newVariantImages.forEach(function(input) {
        const previewContainer = input.nextElementSibling.nextElementSibling;
        if (previewContainer) {
            setupVariantImagePreview(input, previewContainer);
        } else {
            console.log("Preview container not found for:", input);
        }
    });
    
    // Setup image previews for existing variant image inputs
    const variantImages = document.querySelectorAll('.variant-image');
    console.log("Found existing variant image inputs:", variantImages.length);
    variantImages.forEach(function(input) {
        const previewContainer = input.nextElementSibling.nextElementSibling;
        if (previewContainer) {
            setupVariantImagePreview(input, previewContainer);
        } else {
            console.log("Preview container not found for:", input);
        }
    });
    
    // Product image preview
    const imagesInput = document.getElementById('images');
    console.log("Product images input:", imagesInput);
    
    if (imagesInput) {
        imagesInput.addEventListener('change', function(event) {
            console.log("Product images input changed");
            const previewContainer = document.getElementById('product-image-previews');
            const parentContainer = imagesInput.closest('.card-body');
            
            if (previewContainer) {
                previewContainer.innerHTML = ''; // Clear previous previews
                clearSizeWarnings(); // Clear any existing warnings
                
                if (this.files && this.files.length > 0) {
                    // Check if too many files are selected
                    if (this.files.length > MAX_FILES) {
                        const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                            <strong>Warning!</strong> You selected ${this.files.length} files. Maximum ${MAX_FILES} files are allowed.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                        const warningContainer = document.createElement('div');
                        warningContainer.innerHTML = warningHTML;
                        parentContainer.appendChild(warningContainer.firstChild);
                    }
                    console.log("Product images selected:", this.files.length);
                    Array.from(this.files).forEach(file => {
                        // Validate file size
                        validateFileSize(file, parentContainer);
                        
                        // Check file type
                        if (!file.type.match('image/(jpeg|png|gif|jpg)')) {
                            const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                                <strong>Warning!</strong> File "${file.name}" is not a supported format (JPG, PNG, GIF).
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>`;
                            const warningContainer = document.createElement('div');
                            warningContainer.innerHTML = warningHTML;
                            parentContainer.appendChild(warningContainer.firstChild);
                        }
                        
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
                            
                            console.log("Added product image preview for:", file.name);
                        };
                        reader.readAsDataURL(file);
                    });
                }
            } else {
                console.log("Product image preview container not found");
            }
        });
    } else {
        console.log("Product images input not found");
    }
    
    console.log("Script initialization complete");
});
</script>
@endsection