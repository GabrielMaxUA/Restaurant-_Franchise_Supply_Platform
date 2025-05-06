@extends('layouts.warehouse')

@section('title', 'Most Popular Products')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Most Popular Products</h3>
                    <div class="card-tools">
                        <a href="{{ route('warehouse.products.index') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to All Products
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(count($products) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Base Price</th>
                                        <th>Inventory Count</th>
                                        <th class="text-primary">Orders Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>{{ $product->id }}</td>
                                            <td>{{ $product->name }}</td>
                                            <td>{{ $product->category ? $product->category->name : 'No Category' }}</td>
                                            <td>${{ number_format($product->base_price, 2) }}</td>
                                            <td>
                                                @if($product->inventory_count == 0)
                                                    <span class="text-danger">Out of Stock</span>
                                                @elseif($product->inventory_count <= 10)
                                                    <span class="text-warning">{{ $product->inventory_count }} (Low)</span>
                                                @else
                                                    {{ $product->inventory_count }}
                                                @endif
                                            </td>
                                            <td class="text-primary font-weight-bold">{{ $product->orders_count }}</td>
                                            <td>
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="{{ route('warehouse.products.show', $product) }}" class="btn btn-sm btn-info rounded">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('warehouse.products.edit', $product) }}" class="btn btn-sm btn-warning rounded">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('warehouse.products.destroy', $product) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger rounded" onclick="return confirm('Are you sure you want to delete this product?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No products found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('.table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endsection