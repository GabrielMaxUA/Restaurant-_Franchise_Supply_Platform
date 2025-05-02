@extends('layouts.admin')

@section('title', 'Products - Restaurant Franchise Supply Platform')

@section('page-title', 'Products')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Product Management</h1>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add New Product
    </a>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Base Price</th>
                        <th>Inventory</th>
                        <th>Variants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr class="text-center align-middle">
                            <td>
                                @if($product->images->count() > 0)
                                    <img src="{{ asset('storage/' . $product->images->first()->image_url) }}" 
                                         alt="{{ $product->name }}" 
                                         width="75" height="75" 
                                         class="img-thumbnail">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                         style="width: 75px; height: 75px; margin: 0 auto;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                            <td>${{ number_format($product->base_price, 2) }}</td>
                            <td>{{ $product->inventory_count }}</td>
                            <td>{{ $product->variants->count() }}</td>
                            <td>
                              <div class="d-flex gap-1 justify-content-center">
                                  <a href="{{ route('admin.products.show', $product) }}" class="btn btn-sm btn-info rounded">
                                      <i class="fas fa-eye"></i>
                                  </a>
                                  <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning rounded">
                                      <i class="fas fa-edit"></i>
                                  </a>
                                  <form action="{{ route('admin.products.destroy', $product) }}" method="POST">
                                      @csrf
                                      @method('DELETE')
                                      <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('Are you sure you want to delete this product?')">
                                          <i class="fas fa-trash"></i>
                                      </button>
                                  </form>
                              </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection