@extends('layouts.admin')

@section('title', 'Add New Product - Restaurant Franchise Supply Platform')

@section('page-title', 'Add New Product')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Product Details</h6>
    </div>
    <div class="card-body">
        <!-- File Upload Guidelines Box -->
        <div id="file-guidelines-box" class="file-guidelines-box info">
            <div class="icon-wrapper">
                <i id="status-icon" class="fas fa-info-circle"></i>
            </div>
            <div class="content-wrapper">
                <h5>File Upload Guidelines</h5>
                <ul id="guidelines-list">
                    <li>Each image must be under 2MB in size</li>
                    <li>Maximum 5 images can be uploaded at once</li>
                    <li>If you have more images, please add the most important ones first, then add additional images in edit mode</li>
                    <li>Supported formats: JPG, PNG, GIF</li>
                </ul>
                <div id="issues-container" style="display: none;">
                    <h6>Issues found:</h6>
                    <ul id="issues-list"></ul>
                </div>
            </div>
        </div>
        
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
            
            <div class="form-group mb-3 file-section" id="product-images-section">
                <label for="images">Product Images</label>
                <input type="file" class="form-control @error('images') is-invalid @enderror" 
                    id="images" name="images[]" multiple accept="image/*">
                <small class="text-muted">You can select multiple files by holding Ctrl (or Cmd on Mac) while selecting. Supported formats: JPG, PNG, GIF (max 2MB each)</small>
                @error('images')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
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
                                    <label>Price ($)</label>
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
                        
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group file-section" id="variant-image-section-0">
                                    <label>Variant Images (Optional)</label>
                                    <input type="file" class="form-control variant-image" 
                                        name="variant_image_0[]" multiple accept="image/*">
                                    <small class="text-muted">You can select multiple files by holding Ctrl (or Cmd on Mac) while selecting. Supported formats: JPG, PNG, GIF (max 2MB each)</small>
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

@section('styles')
<style>
/* File Guidelines Box */
.file-guidelines-box {
    position: sticky;
    top: 15px;
    margin-bottom: 20px;
    padding: 15px;
    border-radius: 6px;
    display: flex;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    z-index: 1000;
}

.file-guidelines-box .icon-wrapper {
    padding-right: 15px;
    display: flex;
    align-items: flex-start;
    font-size: 24px;
}

.file-guidelines-box .content-wrapper {
    flex: 1;
}

.file-guidelines-box h5 {
    margin-top: 0;
    margin-bottom: 10px;
    font-weight: 600;
}

.file-guidelines-box h6 {
    color: #dc3545;
    font-weight: 600;
}

.file-guidelines-box ul {
    padding-left: 20px;
    margin-bottom: 0;
}

#issues-container {
    margin-top: 15px;
    padding: 10px;
    background-color: rgba(0,0,0,0.03);
    border-radius: 4px;
}

/* Status colors */
.file-guidelines-box.info {
    background-color: #f8f9fa;
    border-left: 5px solid #0dcaf0;
}

.file-guidelines-box.info .icon-wrapper {
    color: #0dcaf0;
}

.file-guidelines-box.success {
    background-color: #f0fff4;
    border-left: 5px solid #198754;
}

.file-guidelines-box.success .icon-wrapper {
    color: #198754;
}

.file-guidelines-box.error {
    background-color: #fff8f8;
    border-left: 5px solid #dc3545;
}

.file-guidelines-box.error .icon-wrapper {
    color: #dc3545;
}

/* Animation */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.file-guidelines-box.animate {
    animation: pulse 0.5s;
}

/* File Section */
.file-section {
    transition: all 0.3s ease;
    padding: 10px;
    border-radius: 4px;
}

.file-section.error-section {
    background-color: rgba(220, 53, 69, 0.05);
    border-left: 3px solid #dc3545;
}

/* Image Preview Styles */
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
    height: 125px !important;
    width: auto !important;
    object-fit: contain !important;
    border-radius: 4px;
}

/* Image Cards */
.variant-image-preview .card,
#product-image-preview .card {
    display: inline-block;
    width: auto;
    margin: 0 auto;
    border: 1px solid rgba(0,0,0,.125);
    overflow: hidden;
}

.variant-image-preview .col-auto,
#product-image-preview .col-auto {
    padding: 0 10px;
}

/* Delete Button */
.btn-danger:hover {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let variantIndex = 0;
    
    // Get the guidelines box elements
    const guidelinesBox = document.getElementById('file-guidelines-box');
    const guidelinesList = document.getElementById('guidelines-list');
    const issuesContainer = document.getElementById('issues-container');
    const issuesList = document.getElementById('issues-list');
    const statusIcon = document.getElementById('status-icon');
    const submitButton = document.getElementById('submit-button');
    
    // Set up file validation constants
    const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB
    const MAX_FILES_PER_INPUT = 5;
    const MAX_TOTAL_FILES = 5; // Maximum 5 files total across all inputs
    
    // Set up product image preview with delete buttons
    setupProductImagePreview();
    
    // Function to set up product image preview with delete buttons
    function setupProductImagePreview() {
        const imageInput = document.getElementById('images');
        const previewContainer = document.getElementById('product-image-preview');
        
        imageInput.addEventListener('change', function(event) {
            previewContainer.innerHTML = ''; // Clear previous previews
            
            if (this.files && this.files.length > 0) {
                // Convert FileList to Array for manipulation
                const filesArray = Array.from(this.files);
                
                // Create previews
                filesArray.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-auto mb-2';
                        col.dataset.fileIndex = index;
                        
                        const card = document.createElement('div');
                        card.className = 'card d-inline-block';
                        
                        // Add image
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'card-img-top';
                        img.style.height = '125px';
                        img.style.width = 'auto';
                        img.style.objectFit = 'contain';
                        img.alt = 'Product Image Preview';
                        
                        // Add delete button
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body p-2 text-center';
                        
                        const deleteBtn = document.createElement('button');
                        deleteBtn.type = 'button';
                        deleteBtn.className = 'btn btn-sm btn-danger';
                        deleteBtn.textContent = 'Delete';
                        deleteBtn.addEventListener('click', function() {
                            col.remove();
                            
                            // Recreate the FileList based on the remaining previews
                            recreateFileList(imageInput, previewContainer);
                            
                            // Validate files
                            validateAllFiles();
                        });
                        
                        cardBody.appendChild(deleteBtn);
                        card.appendChild(img);
                        card.appendChild(cardBody);
                        col.appendChild(card);
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
                
                // Validate files immediately after selection
                validateAllFiles();
            } else {
                // No files selected
                validateAllFiles();
            }
        });
    }
    
    // Function to set up variant image preview with delete buttons
    function setupVariantImagePreview(variantImageInput, previewContainer) {
        variantImageInput.addEventListener('change', function(event) {
            previewContainer.innerHTML = ''; // Clear previous preview
            
            if (this.files && this.files.length > 0) {
                // Convert FileList to Array for manipulation
                const filesArray = Array.from(this.files);
                
                // Create previews
                filesArray.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-auto mb-2';
                        col.dataset.fileIndex = index;
                        
                        const card = document.createElement('div');
                        card.className = 'card d-inline-block';
                        
                        // Add image
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'card-img-top';
                        img.style.height = '125px';
                        img.style.width = 'auto';
                        img.style.objectFit = 'contain';
                        img.alt = 'Variant Image Preview';
                        
                        // Add delete button
                        const cardBody = document.createElement('div');
                        cardBody.className = 'card-body p-2 text-center';
                        
                        const deleteBtn = document.createElement('button');
                        deleteBtn.type = 'button';
                        deleteBtn.className = 'btn btn-sm btn-danger';
                        deleteBtn.textContent = 'Delete';
                        deleteBtn.addEventListener('click', function() {
                            col.remove();
                            
                            // Recreate the FileList based on the remaining previews
                            recreateFileList(variantImageInput, previewContainer);
                            
                            // Validate files
                            validateAllFiles();
                        });
                        
                        cardBody.appendChild(deleteBtn);
                        card.appendChild(img);
                        card.appendChild(cardBody);
                        col.appendChild(card);
                        previewContainer.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                });
                
                // Validate files immediately after selection
                validateAllFiles();
            } else {
                // No files selected
                validateAllFiles();
            }
        });
    }
    
    // Helper function to recreate FileList after preview deletion
    // This is a workaround since FileList is read-only
    function recreateFileList(inputElement, previewContainer) {
        // Create a new DataTransfer object
        const dataTransfer = new DataTransfer();
        
        // Get all preview elements
        const previews = previewContainer.querySelectorAll('.col-auto');
        
        // If there are no previews left, clear the input
        if (previews.length === 0) {
            inputElement.value = '';
            return;
        }
        
        // Otherwise, create a new file list with remaining files
        const originalFiles = Array.from(inputElement.files);
        
        previews.forEach(preview => {
            const fileIndex = parseInt(preview.dataset.fileIndex);
            if (fileIndex >= 0 && fileIndex < originalFiles.length) {
                dataTransfer.items.add(originalFiles[fileIndex]);
            }
        });
        
        // Update the file input with our new FileList
        inputElement.files = dataTransfer.files;
    }
    
    // Setup initial variant image preview
    const initialVariantImage = document.querySelector('.variant-image');
    const initialPreviewContainer = document.querySelector('.variant-image-preview');
    if (initialVariantImage && initialPreviewContainer) {
        setupVariantImagePreview(initialVariantImage, initialPreviewContainer);
    }
    
    // Handle placeholder removal and add validation classes on focus for price inputs
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
    
    // Handle placeholder removal for inventory count inputs (variants)
    function setupInventoryInputs() {
        document.querySelectorAll('input[name^="variants"][name$="[inventory_count]"]').forEach(input => {
            // Clear default value when focused
            input.addEventListener('focus', function() {
                if (this.value === '0') {
                    this.value = '';
                }
            });
            
            // Restore default if left empty
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = '0';
                }
            });
        });
    }
    
    // Initial setup for inventory inputs
    setupInventoryInputs();
    
    // Function to clear all size warnings
    function clearSizeWarnings() {
        document.querySelectorAll('.alert-warning').forEach(warning => {
            warning.remove();
        });
    }
    
    // Main function to validate all files and update UI
    function validateAllFiles() {
        // Reset all section error highlighting and clear existing warnings
        document.querySelectorAll('.file-section').forEach(section => {
            section.classList.remove('error-section');
        });
        clearSizeWarnings();
        
        let allValid = true;
        let anyFiles = false;
        let issues = [];
        let totalFiles = 0; // Track total number of files across all inputs
        
        // Validate product images
        const productImagesInput = document.getElementById('images');
        if (productImagesInput && productImagesInput.files.length > 0) {
            anyFiles = true;
            totalFiles += productImagesInput.files.length;
            
            // Check file count for this input
            if (productImagesInput.files.length > MAX_FILES_PER_INPUT) {
                allValid = false;
                issues.push(`Too many files selected for Product Images: ${productImagesInput.files.length} files (max ${MAX_FILES_PER_INPUT} per input)`);
                document.getElementById('product-images-section').classList.add('error-section');
            }
            
            // Check file sizes and types
            for (let i = 0; i < productImagesInput.files.length; i++) {
                const file = productImagesInput.files[i];
                
                if (file.size > MAX_FILE_SIZE) {
                    allValid = false;
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    issues.push(`File "${file.name}" is ${fileSizeMB}MB (max 2MB)`);
                    document.getElementById('product-images-section').classList.add('error-section');
                    
                    // Show a flash warning message
                    const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                        <strong>Warning!</strong> File "${file.name}" (${fileSizeMB}MB) exceeds the 2MB size limit and will be rejected.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    const warningContainer = document.createElement('div');
                    warningContainer.innerHTML = warningHTML;
                    document.getElementById('product-images-section').appendChild(warningContainer.firstChild);
                }
                
                if (!file.type.match('image/(jpeg|png|gif|jpg)')) {
                    allValid = false;
                    issues.push(`File "${file.name}" is not a supported format (JPG, PNG, GIF)`);
                    document.getElementById('product-images-section').classList.add('error-section');
                }
            }
        }
        
        // Validate all variant images
        document.querySelectorAll('.variant-image').forEach((input, idx) => {
            if (input.files.length > 0) {
                anyFiles = true;
                totalFiles += input.files.length;
                let section = input.closest('.file-section');
                
                // Check file count for this input
                if (input.files.length > MAX_FILES_PER_INPUT) {
                    allValid = false;
                    issues.push(`Too many files selected for Variant Image ${idx + 1}: ${input.files.length} files (max ${MAX_FILES_PER_INPUT} per input)`);
                    section.classList.add('error-section');
                }
                
                // Check file sizes and types
                for (let i = 0; i < input.files.length; i++) {
                    const file = input.files[i];
                    
                    if (file.size > MAX_FILE_SIZE) {
                        allValid = false;
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        issues.push(`File "${file.name}" is ${fileSizeMB}MB (max 2MB)`);
                        section.classList.add('error-section');
                        
                        // Show a flash warning message for the variant
                        const warningHTML = `<div class="alert alert-warning alert-dismissible fade show mt-2" role="alert">
                            <strong>Warning!</strong> Variant image "${file.name}" (${fileSizeMB}MB) exceeds the 2MB size limit and will be rejected.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>`;
                        const warningContainer = document.createElement('div');
                        warningContainer.innerHTML = warningHTML;
                        section.appendChild(warningContainer.firstChild);
                    }
                    
                    if (!file.type.match('image/(jpeg|png|gif|jpg)')) {
                        allValid = false;
                        issues.push(`File "${file.name}" is not a supported format (JPG, PNG, GIF)`);
                        section.classList.add('error-section');
                    }
                }
            }
        });
        
        // Check total file count across all inputs
        if (totalFiles > MAX_TOTAL_FILES) {
            allValid = false;
            issues.push(`Too many files selected in total: ${totalFiles} files (maximum ${MAX_TOTAL_FILES} files allowed across all uploads)`);
            document.querySelectorAll('.file-section').forEach(section => {
                section.classList.add('error-section');
            });
        }
        
        // Update submit button state based on validation
        if (!allValid) {
            submitButton.disabled = true;
            submitButton.classList.add('disabled');
        } else {
            submitButton.disabled = false;
            submitButton.classList.remove('disabled');
        }
        
        // Update the guidelines box UI
        updateGuidelinesBoxUI(allValid, anyFiles, issues, totalFiles);
        
        return allValid;
    }
    
    // Function to update the guidelines box UI
    function updateGuidelinesBoxUI(isValid, hasFiles, issues, totalFiles) {
        // Reset classes first
        guidelinesBox.classList.remove('info', 'success', 'error', 'animate');
        
        // Add animation class
        guidelinesBox.classList.add('animate');
        setTimeout(() => {
            guidelinesBox.classList.remove('animate');
        }, 500);
        
        // If no files selected, show info state
        if (!hasFiles) {
            guidelinesBox.classList.add('info');
            statusIcon.className = 'fas fa-info-circle';
            issuesContainer.style.display = 'none';
            
            // Regular guidelines
            guidelinesList.innerHTML = `
                <li>Each image must be under 2MB in size</li>
                <li>Maximum ${MAX_TOTAL_FILES} images can be uploaded in total across all fields</li>
                <li>If you have more images, please add the most important ones first, then add additional images in edit mode</li>
                <li>Supported formats: JPG, PNG, GIF</li>
            `;
            return;
        }
        
        if (isValid) {
            // Success state
            guidelinesBox.classList.add('success');
            statusIcon.className = 'fas fa-check-circle';
            issuesContainer.style.display = 'none';
            
            // Success guidelines
            guidelinesList.innerHTML = `
                <li>All images are under 2MB in size ✓</li>
                <li>${totalFiles} of ${MAX_TOTAL_FILES} total images used ✓</li>
                <li>All files are in supported formats (JPG, PNG, GIF) ✓</li>
            `;
        } else {
            // Error state
            guidelinesBox.classList.add('error');
            statusIcon.className = 'fas fa-exclamation-triangle';
            
            // Regular guidelines
            guidelinesList.innerHTML = `
                <li>Each image must be under 2MB in size</li>
                <li>Maximum ${MAX_TOTAL_FILES} images can be uploaded in total across all fields</li>
                <li>If you have more images, please add the most important ones first, then add additional images in edit mode</li>
                <li>Supported formats: JPG, PNG, GIF</li>
            `;
            
            // Update issues list
            issuesList.innerHTML = '';
            issues.forEach(issue => {
                const li = document.createElement('li');
                li.textContent = issue;
                issuesList.appendChild(li);
            });
            
            issuesContainer.style.display = 'block';
        }
    }
    
    // Handle adding new variants
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
                <div class=Price
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
                    <input type="number" min="0" class="form-control variant-inventory" 
                        name="variants[${variantIndex}][inventory_count]" value="0"
                        placeholder="Enter quantity">
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <div class="form-group file-section" id="variant-image-section-${variantIndex}">
                    <label>Variant Images (Optional)</label>
                    <input type="file" class="form-control variant-image" 
                        name="variant_image_${variantIndex}[]" multiple accept="image/*">
                    <small class="text-muted">You can select multiple files by holding Ctrl (or Cmd on Mac) while selecting. Supported formats: JPG, PNG, GIF (max 2MB each)</small>
                    <div class="variant-image-preview mt-2 row"></div>
                </div>
            </div>
        </div>
        
        <button type="button" class="btn btn-sm btn-danger mt-2 remove-variant">
            <i class="fas fa-trash me-2"></i>Remove Variant
        </button>
    </div>
</div>`;
        
        document.getElementById('variants-container').insertAdjacentHTML('beforeend', variantTemplate);
        
        // Setup image preview for the new variant
        const newVariantImage = document.querySelector(`.variant-card:last-child .variant-image`);
        const newPreviewContainer = document.querySelector(`.variant-card:last-child .variant-image-preview`);
        setupVariantImagePreview(newVariantImage, newPreviewContainer);
        
        // Add event listeners to new remove buttons
        document.querySelectorAll('.remove-variant').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.variant-card').remove();
                // Validate files after removing variant
                validateAllFiles();
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
        
        // Add event listeners to new inventory count inputs
        const newInventoryInputs = document.querySelectorAll(`.variant-card:last-child input[name$="[inventory_count]"]`);
        newInventoryInputs.forEach(input => {
            input.addEventListener('focus', function() {
                if (this.value === '0') {
                    this.value = '';
                }
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = '0';
                }
            });
        });
        
        // Validate all files after adding new variant
        validateAllFiles();
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
        
        // Validate all files
        const filesValid = validateAllFiles();
        if (!filesValid) {
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Scroll to the first error
            const firstError = document.querySelector('.is-invalid, .error-section');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else if (!filesValid) {
                guidelinesBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            // Show alert if file issues exist
            if (!filesValid) {
                alert('Please fix the file upload issues before submitting the form.');
            }
        }
    });
    
    // Also apply the placeholder behavior to the main inventory count input
    const mainInventoryInput = document.getElementById('inventory_count');
    if (mainInventoryInput) {
        mainInventoryInput.addEventListener('focus', function() {
            if (this.value === '0') {
                this.value = '';
            }
        });
        
        mainInventoryInput.addEventListener('blur', function() {
            if (this.value === '') {
                this.value = '0';
            }
        });
    }
    
    // Run initial validation
    validateAllFiles();
    
    // Monitor DOM changes to detect when file inputs change without firing events
    // This is important for when JavaScript modifies the files but doesn't trigger change events
    const observer = new MutationObserver(function(mutations) {
        let shouldValidate = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && 
                (mutation.target.classList.contains('variant-image-preview') || 
                 mutation.target.id === 'product-image-preview')) {
                shouldValidate = true;
            }
        });
        
        if (shouldValidate) {
            validateAllFiles();
        }
    });
    
    // Start observing
    observer.observe(document.getElementById('product-form'), { 
        childList: true, 
        subtree: true 
    });
});
</script>
@endsection