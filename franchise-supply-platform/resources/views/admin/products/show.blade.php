@extends('layouts.admin')

@section('title', 'Product Details - Restaurant Franchise Supply Platform')

@section('page-title', 'Product Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
</div>

<div class="row">
    <!-- Product Images -->
    <div class="col-md-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Product Images</h6>
            </div>
            <div class="card-body text-center">
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
                    
                    <!-- Thumbnails with fixed height -->
                    @if($product->images->count() > 1)
                        <div class="mt-3">
                            <div class="row">
                                @foreach($product->images as $index => $image)
                                    <div class="col-3 mb-2">
                                        <a href="#" data-bs-target="#productImageCarousel" data-bs-slide-to="{{ $index }}" class="d-block">
                                            <div style="height: 60px; width: 100%; overflow: hidden;">
                                                <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                     class="img-thumbnail" 
                                                     style="object-fit: cover; height: 100%; width: 100%;"
                                                     alt="Thumbnail {{ $index + 1 }}">
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <div class="mt-3 text-muted">
                        <small>{{ $product->images->count() }} image(s) available</small>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-image fa-5x text-muted mb-3"></i>
                        <p class="text-muted">No images available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Product Details -->
    <div class="col-md-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Product Information</h6>
                <div>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">
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
                       <!-- Inside your variants table in show.blade.php -->
                      <table class="table table-bordered table-striped">
                          <thead class="table-light">
                              <tr>
                                  <th>Image</th> <!-- New column -->
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
                                          @if($variant->image_url)
                                              <img src="{{ asset('storage/' . $variant->image_url) }}" 
                                                   class="img-thumbnail" 
                                                   style="height: 50px; width: 50px; object-fit: cover;"
                                                   alt="{{ $variant->name }} image">
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