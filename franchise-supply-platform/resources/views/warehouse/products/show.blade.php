@extends('layouts.warehouse')

@section('title', 'Product Details - Restaurant Franchise Supply Platform')

@section('page-title', 'Product Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('warehouse.products.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
</div>

<div class="row">
    <!-- Product Images -->
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary" id="current-gallery-title">Product Images</h6>
                <div id="return-to-product" style="display: none;">
                    <button class="btn btn-sm btn-outline-secondary return-product-btn">
                        <i class="fas fa-arrow-left me-1"></i> Back to Product
                    </button>
                </div>
            </div>
            <div class="card-body text-center">
                <!-- Main Product Carousel -->
                <div id="main-product-carousel" class="gallery-container">
                    @if($product->images->count() > 0)
                        <div id="productImageCarousel" class="carousel slide" data-bs-ride="carousel">
                            <!-- Carousel indicators for multiple images -->
                            @if($product->images->count() > 1)
                                <div class="carousel-indicators">
                                    @foreach($product->images as $index => $image)
                                        <button type="button" data-bs-target="#productImageCarousel" 
                                            data-bs-slide-to="{{ $index }}" 
                                            class="{{ $index == 0 ? 'active' : '' }}"
                                            aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                            aria-label="Slide {{ $index + 1 }}"></button>
                                    @endforeach
                                </div>
                            @endif
                            
                            <!-- Fixed height carousel container -->
                            <div class="carousel-inner" style="height: 300px;">
                                @foreach($product->images as $index => $image)
                                    <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                        <img src="{{ asset('storage/' . $image->image_url) }}" 
                                             class="d-block w-100 h-100 img-fluid" 
                                             style="object-fit: contain;"
                                             alt="{{ $product->name }} image {{ $index + 1 }}">
                                    </div>
                                @endforeach
                            </div>
                            
                            <!-- Enhanced navigation arrows -->
                            @if($product->images->count() > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#productImageCarousel" data-bs-slide="prev" style="background: rgba(0,0,0,0.2); width: 40px; height: 40px; border-radius: 50%; top: 50%; transform: translateY(-50%); margin-left: 10px;">
                                    <span class="carousel-control-prev-icon" style="width: 24px; height: 24px;" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#productImageCarousel" data-bs-slide="next" style="background: rgba(0,0,0,0.2); width: 40px; height: 40px; border-radius: 50%; top: 50%; transform: translateY(-50%); margin-right: 10px;">
                                    <span class="carousel-control-next-icon" style="width: 24px; height: 24px;" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            @endif
                        </div>
                        
                        <!-- Product and Variant Thumbnails -->
                        <div class="mt-3" id="gallery-thumbnails">
                            <div class="row">
                                <!-- Product Thumbnails -->
                                @if($product->images->count() > 0)
                                    <div class="col-12 mb-2">
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            @foreach($product->images as $index => $image)
                                                <div class="thumbnail-wrapper product-thumb" style="width: 60px; height: 60px; overflow: hidden;">
                                                    <a href="#" data-bs-target="#productImageCarousel" data-bs-slide-to="{{ $index }}" class="d-block product-image-thumb">
                                                        <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                             class="img-thumbnail" 
                                                             style="object-fit: cover; height: 100%; width: 100%;"
                                                             alt="Thumbnail {{ $index + 1 }}">
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Variant Thumbnails -->
                                @if($product->variants->count() > 0)
                                    <div class="col-12">
                                        <hr class="my-2">
                                        <small class="text-muted d-block mb-2">Variant Images</small>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            @foreach($product->variants as $variant)
                                                @if($variant->images && $variant->images->count() > 0)
                                                    <div class="thumbnail-wrapper variant-thumb" style="width: 60px; height: 60px; overflow: hidden; position: relative;">
                                                        <a href="#" class="d-block variant-thumbnail" data-variant-id="{{ $variant->id }}">
                                                            <img src="{{ asset('storage/' . $variant->images->first()->image_url) }}" 
                                                                 class="img-thumbnail" 
                                                                 style="object-fit: cover; height: 100%; width: 100%;"
                                                                 alt="{{ $variant->name }} thumbnail">
                                                            <div class="variant-label" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.5); color: white; font-size: 9px; padding: 2px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                {{ $variant->name }}
                                                            </div>
                                                            @if($variant->images->count() > 1)
                                                                <div class="badge bg-info rounded-pill position-absolute" style="top: 2px; right: 2px; font-size: 0.6rem;">
                                                                    {{ $variant->images->count() }}
                                                                </div>
                                                            @endif
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="mt-3 text-muted">
                            <small id="image-counter">{{ $product->images->count() }} product image(s) available</small>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-image fa-5x text-muted mb-3"></i>
                            <p class="text-muted">No images available</p>
                        </div>
                    @endif
                </div>
                
                <!-- Variant Carousels - Hidden Initially -->
                @foreach($product->variants as $variant)
                    @if($variant->images && $variant->images->count() > 0)
                        <div id="variant-carousel-{{ $variant->id }}" class="gallery-container" style="display: none;">
                            <div id="variantImageCarousel{{ $variant->id }}" class="carousel slide" data-bs-ride="carousel">
                                <!-- Carousel indicators for multiple images -->
                                @if($variant->images->count() > 1)
                                    <div class="carousel-indicators">
                                        @foreach($variant->images as $index => $image)
                                            <button type="button" data-bs-target="#variantImageCarousel{{ $variant->id }}" 
                                                data-bs-slide-to="{{ $index }}" 
                                                class="{{ $index == 0 ? 'active' : '' }}"
                                                aria-current="{{ $index == 0 ? 'true' : 'false' }}"
                                                aria-label="Slide {{ $index + 1 }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Fixed height carousel container -->
                                <div class="carousel-inner" style="height: 300px;">
                                    @foreach($variant->images as $index => $image)
                                        <div class="carousel-item h-100 {{ $index == 0 ? 'active' : '' }}">
                                            <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                 class="d-block w-100 h-100 img-fluid" 
                                                 style="object-fit: contain;"
                                                 alt="{{ $variant->name }} image {{ $index + 1 }}">
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Enhanced navigation arrows -->
                                @if($variant->images->count() > 1)
                                    <button class="carousel-control-prev" type="button" data-bs-target="#variantImageCarousel{{ $variant->id }}" data-bs-slide="prev" style="background: rgba(0,0,0,0.2); width: 40px; height: 40px; border-radius: 50%; top: 50%; transform: translateY(-50%); margin-left: 10px;">
                                        <span class="carousel-control-prev-icon" style="width: 24px; height: 24px;" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#variantImageCarousel{{ $variant->id }}" data-bs-slide="next" style="background: rgba(0,0,0,0.2); width: 40px; height: 40px; border-radius: 50%; top: 50%; transform: translateY(-50%); margin-right: 10px;">
                                        <span class="carousel-control-next-icon" style="width: 24px; height: 24px;" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                @endif
                            </div>
                            
                            <!-- Variant Thumbnails -->
                            <div class="mt-3">
                                <div class="row">
                                    <div class="col-12 mb-2">
                                        <!-- Variant's own image thumbnails -->
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            @foreach($variant->images as $index => $image)
                                                <div class="thumbnail-wrapper" style="width: 60px; height: 60px; overflow: hidden;">
                                                    <a href="#" data-bs-target="#variantImageCarousel{{ $variant->id }}" data-bs-slide-to="{{ $index }}" class="d-block">
                                                        <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                             class="img-thumbnail" 
                                                             style="object-fit: cover; height: 100%; width: 100%;"
                                                             alt="Variant Thumbnail {{ $index + 1 }}">
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <hr class="my-2">
                                        <small class="text-muted d-block mb-2">Other Images</small>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                                            <!-- Product Thumbnail Button to switch back -->
                                            <div class="thumbnail-wrapper" style="width: 60px; height: 60px; overflow: hidden; position: relative;">
                                                <a href="#" class="d-block return-product-thumbnail">
                                                    @if($product->images->count() > 0)
                                                        <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                                             class="img-thumbnail" 
                                                             style="object-fit: cover; height: 100%; width: 100%;"
                                                             alt="Product thumbnail">
                                                    @else
                                                        <div class="img-thumbnail d-flex align-items-center justify-content-center" style="height: 100%; width: 100%;">
                                                            <i class="fas fa-box"></i>
                                                        </div>
                                                    @endif
                                                    <div class="variant-label" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.5); color: white; font-size: 9px; padding: 2px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        Product
                                                    </div>
                                                </a>
                                            </div>
                                            
                                            <!-- Other Variants Thumbnails -->
                                            @foreach($product->variants as $otherVariant)
                                                @if($otherVariant->id != $variant->id && $otherVariant->images && $otherVariant->images->count() > 0)
                                                    <div class="thumbnail-wrapper" style="width: 60px; height: 60px; overflow: hidden; position: relative;">
                                                        <a href="#" class="d-block variant-thumbnail" data-variant-id="{{ $otherVariant->id }}">
                                                            <img src="{{ asset('storage/' . $otherVariant->images->first()->image_url) }}" 
                                                                 class="img-thumbnail" 
                                                                 style="object-fit: cover; height: 100%; width: 100%;"
                                                                 alt="{{ $otherVariant->name }} thumbnail">
                                                            <div class="variant-label" style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.5); color: white; font-size: 9px; padding: 2px; text-align: center; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                                {{ $otherVariant->name }}
                                                            </div>
                                                            @if($otherVariant->images->count() > 1)
                                                                <div class="badge bg-info rounded-pill position-absolute" style="top: 2px; right: 2px; font-size: 0.6rem;">
                                                                    {{ $otherVariant->images->count() }}
                                                                </div>
                                                            @endif
                                                        </a>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-muted">
                                <small>{{ $variant->images->count() }} image(s) for variant: {{ $variant->name }}</small>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-md-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
                <div>
                    <a href="{{ route('warehouse.products.edit', $product) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit me-1"></i> Edit Product
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Product ID:</div>
                    <div class="col-md-9">{{ $product->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Name:</div>
                    <div class="col-md-9">{{ $product->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Category:</div>
                    <div class="col-md-9">
                        @if($product->category)
                            <span class="badge bg-info">{{ $product->category->name }}</span>
                        @else
                            <span class="badge bg-secondary">Uncategorized</span>
                        @endif
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Base Price:</div>
                    <div class="col-md-9">${{ number_format($product->base_price, 2) }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Inventory:</div>
                    <div class="col-md-9">
                        <span class="badge {{ $product->inventory_count > 10 ? 'bg-success' : 'bg-danger' }}">
                            {{ $product->inventory_count }} in stock
                        </span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Created:</div>
                    <div class="col-md-9">{{ $product->created_at }}</div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3 fw-bold">Description:</div>
                    <div class="col-md-9">
                        {!! nl2br(e($product->description)) ?? '<span class="text-muted">No description provided</span>' !!}
                    </div>
                </div>
                
                <hr>
                
                <h5 class="mt-4 mb-3">Product Variants</h5>
                @if($product->variants->count() > 0)
                    <div class="table-responsive">
                       <table class="table table-bordered table-striped">
                          <thead class="table-light">
                              <tr>
                                  <th>Image</th>
                                  <th>Name</th>
                                  <th>Price Adjustment</th>
                                  <th>Final Price</th>
                                  <th>Inventory</th>
                              </tr>
                          </thead>
                          <tbody>
                              @foreach($product->variants as $variant)
                                  <tr>
                                      <td class="text-center">
                                          @if($variant->images && $variant->images->count() > 0)
                                              <div class="variant-table-image" data-variant-id="{{ $variant->id }}">
                                                  <img src="{{ asset('storage/' . $variant->images->first()->image_url) }}" 
                                                       class="img-thumbnail cursor-pointer" 
                                                       style="height: 50px; width: 50px; object-fit: cover; cursor: pointer;"
                                                       alt="{{ $variant->name }} image">
                                                  @if($variant->images->count() > 1)
                                                      <div class="badge bg-info rounded-pill position-absolute" style="top: -5px; right: -5px; font-size: 0.6rem;">
                                                          {{ $variant->images->count() }}
                                                      </div>
                                                  @endif
                                              </div>
                                          @else
                                              <span class="text-muted"><i class="fas fa-image fa-2x text-light"></i></span>
                                          @endif
                                      </td>
                                      <td>{{ $variant->name }}</td>
                                      <td>
                                          @if($variant->price_adjustment > 0)
                                              <span class="text-success">+${{ number_format($variant->price_adjustment, 2) }}</span>
                                          @elseif($variant->price_adjustment < 0)
                                              <span class="text-danger">-${{ number_format(abs($variant->price_adjustment), 2) }}</span>
                                          @else
                                              <span class="text-muted">$0.00</span>
                                          @endif
                                      </td>
                                      <td>${{ number_format($product->base_price + $variant->price_adjustment, 2) }}</td>
                                      <td>
                                          <span class="badge {{ $variant->inventory_count > 10 ? 'bg-success' : 'bg-danger' }}">
                                              {{ $variant->inventory_count }} in stock
                                          </span>
                                      </td>
                                  </tr>
                              @endforeach
                          </tbody>
                      </table>
                    </div>
                @else
                    <div class="alert alert-light text-center">
                        <i class="fas fa-info-circle me-2"></i> No variants available for this product
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .variant-table-image {
    position: relative;
    display: inline-block;
}

.cursor-pointer {
    cursor: pointer;
}

.gallery-container {
    transition: all 0.3s ease;
}

.return-product-btn {
    transition: all 0.2s ease;
}

.return-product-btn:hover {
    transform: translateX(-2px);
}

.variant-label {
    opacity: 0.9;
    transition: all 0.2s ease;
    font-size: 12px; /* Increased from 9px */
    padding: 4px; /* Increased from 2px */
}

.variant-thumbnail:hover .variant-label,
.return-product-thumbnail:hover .variant-label {
    opacity: 1;
    background: rgba(0,0,0,0.7);
}

.thumbnail-wrapper {
    transition: all 0.2s ease;
    border: 2px solid transparent;
    width: 140px !important; /* Fixed size of 100px */
    height: 140px !important; /* Fixed size of 100px */
    margin: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    padding: 5px;
}

.thumbnail-wrapper:hover {
    transform: scale(1.05);
}

.thumbnail-wrapper.active-thumb {
    border: 2px solid #4e73df;
}

.thumbnail-wrapper img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.badge.rounded-pill {
    font-size: 0.7rem !important; /* Increased from 0.6rem */
    top: 3px !important;
    right: 3px !important;
    padding: 3px 6px;
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Element References ---
    const mainProductCarousel = document.getElementById('main-product-carousel');
    const returnToProductBtn = document.getElementById('return-to-product');
    const galleryTitle = document.getElementById('current-gallery-title');
    const imageCounter = document.getElementById('image-counter');
    
    // --- Disable Auto Sliding for Carousels ---
    // Stop product carousel from auto-sliding
    const productCarousel = document.getElementById('productImageCarousel');
    if (productCarousel) {
        // Remove data-bs-ride attribute to prevent auto-sliding
        productCarousel.removeAttribute('data-bs-ride');
        
        // Add event listener for slide change
        productCarousel.addEventListener('slide.bs.carousel', function(event) {
            // Update active thumbnail based on slide index
            updateActiveThumbnail('product', event.to);
        });
    }
    
    // Stop variant carousels from auto-sliding and add slide event listeners
    document.querySelectorAll('[id^="variantImageCarousel"]').forEach(carousel => {
        carousel.removeAttribute('data-bs-ride');
        
        // Extract variant ID from the carousel ID
        const variantId = carousel.id.replace('variantImageCarousel', '');
        
        // Add event listener for slide change
        carousel.addEventListener('slide.bs.carousel', function(event) {
            // Update active thumbnail based on slide index
            updateActiveThumbnail('variant', event.to, variantId);
        });
    });
    
    // --- Event Handlers ---
    
    // 1. Setup variant thumbnail clicks
    document.querySelectorAll('.variant-thumbnail').forEach(thumbnail => {
        thumbnail.addEventListener('click', function(e) {
            e.preventDefault();
            const variantId = this.dataset.variantId;
            showVariantGallery(variantId);
        });
    });
    
    // 2. Setup variant table image clicks
    document.querySelectorAll('.variant-table-image').forEach(image => {
        image.addEventListener('click', function() {
            const variantId = this.dataset.variantId;
            showVariantGallery(variantId);
            
            // Scroll to the gallery for better user experience
            scrollToGallery();
        });
    });
    
    // 3. Setup return to product buttons
    document.querySelectorAll('.return-product-btn, .return-product-thumbnail').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showProductGallery();
        });
    });
    
    // 4. Handle product thumbnail clicks
    document.querySelectorAll('.product-image-thumb').forEach((thumb, index) => {
        thumb.addEventListener('click', function(e) {
            // The data-bs-slide-to attribute will handle the carousel slide change
            // We still need to update active thumbnails manually
            updateActiveThumbnail('product', index);
        });
    });
    
    // --- Helper Functions ---
    
    // Function to update active thumbnail based on carousel slide
    function updateActiveThumbnail(type, slideIndex, variantId = null) {
        if (type === 'product') {
            // Clear all product thumbnails
            document.querySelectorAll('.product-thumb').forEach(thumb => {
                thumb.classList.remove('active-thumb');
            });
            
            // Activate the thumbnail corresponding to the slide index
            const activeThumb = document.querySelector(`.product-thumb a[data-bs-slide-to="${slideIndex}"]`);
            if (activeThumb && activeThumb.closest('.thumbnail-wrapper')) {
                activeThumb.closest('.thumbnail-wrapper').classList.add('active-thumb');
            }
        } else if (type === 'variant' && variantId) {
            // Clear all thumbnails for this specific variant
            document.querySelectorAll(`#variant-carousel-${variantId} .thumbnail-wrapper`).forEach(thumb => {
                thumb.classList.remove('active-thumb');
            });
            
            // Activate the thumbnail corresponding to the slide index
            const activeThumb = document.querySelector(`#variant-carousel-${variantId} .thumbnail-wrapper a[data-bs-slide-to="${slideIndex}"]`);
            if (activeThumb && activeThumb.closest('.thumbnail-wrapper')) {
                activeThumb.closest('.thumbnail-wrapper').classList.add('active-thumb');
            }
        }
    }
    
    // Function to scroll to gallery
    function scrollToGallery() {
        const galleryCard = document.querySelector('.card.shadow');
        if (galleryCard) {
            galleryCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    // Function to clear all active thumbnail states
    function clearActiveThumbnails() {
        document.querySelectorAll('.thumbnail-wrapper').forEach(thumb => {
            thumb.classList.remove('active-thumb');
        });
    }
    
    // Function to show variant gallery
    function showVariantGallery(variantId) {
        // 1. Hide all galleries
        document.querySelectorAll('.gallery-container').forEach(gallery => {
            gallery.style.display = 'none';
        });
        
        // 2. Show selected variant gallery
        const variantGallery = document.getElementById(`variant-carousel-${variantId}`);
        if (variantGallery) {
            variantGallery.style.display = 'block';
            
            // 3. Get the variant name for the title
            let variantName = '';
            
            // Try to get name from table cell
            const tableCell = document.querySelector(`tr [data-variant-id="${variantId}"] + td`);
            if (tableCell) {
                variantName = tableCell.textContent.trim();
            } else {
                // Fallback to label in thumbnail
                const labelElement = document.querySelector(`[data-variant-id="${variantId}"] .variant-label`);
                if (labelElement) {
                    variantName = labelElement.textContent.trim();
                }
            }
            
            // 4. Update UI elements
            galleryTitle.textContent = `${variantName} Images`;
            returnToProductBtn.style.display = 'block';
            
            // 5. Clear all thumbnails and activate the first thumbnail of this variant
            clearActiveThumbnails();
            updateActiveThumbnail('variant', 0, variantId);
            
            // 6. Also activate the variant thumbnail in the list
            const variantThumb = document.querySelector(`.variant-thumb a[data-variant-id="${variantId}"]`);
            if (variantThumb && variantThumb.closest('.thumbnail-wrapper')) {
                variantThumb.closest('.thumbnail-wrapper').classList.add('active-thumb');
            }
        }
    }
    
    // Function to show product gallery
    function showProductGallery() {
        // 1. Hide all galleries
        document.querySelectorAll('.gallery-container').forEach(gallery => {
            gallery.style.display = 'none';
        });
        
        // 2. Show product gallery
        mainProductCarousel.style.display = 'block';
        
        // 3. Update UI elements
        galleryTitle.textContent = 'Product Images';
        returnToProductBtn.style.display = 'none';
        
        // 4. Get the current active slide index
        const activeSlide = document.querySelector('#productImageCarousel .carousel-item.active');
        let slideIndex = 0;
        if (activeSlide) {
            const slides = document.querySelectorAll('#productImageCarousel .carousel-item');
            slideIndex = Array.from(slides).indexOf(activeSlide);
        }
        
        // 5. Update active thumbnail
        clearActiveThumbnails();
        updateActiveThumbnail('product', slideIndex);
    }
    
    // --- Initialization ---
    
    // Set initial state - activate only first product thumbnail
    clearActiveThumbnails();
    updateActiveThumbnail('product', 0);
});
</script>
@endsection