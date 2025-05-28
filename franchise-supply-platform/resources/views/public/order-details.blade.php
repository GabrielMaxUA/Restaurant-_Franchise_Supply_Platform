<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #{{ $order->id }} - {{ config('app.name', 'Restaurant Supply Platform') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Custom Styles -->
    <link href="{{ asset('css/status-styles.css') }}" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .order-details-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            background-color: #fff;
        }
        .order-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
        }
        .order-item {
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .timeline-item {
            position: relative;
            padding-left: 30px;
            margin-bottom: 20px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 8px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #6c757d;
        }
        .timeline-item.active::before {
            background-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
        }
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 11px;
            top: 16px;
            width: 2px;
            height: calc(100% + 12px);
            background-color: #e9ecef;
        }
        .timeline-item:last-child::after {
            display: none;
        }
        .login-prompt {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Simple Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="/">
                <img src="{{ asset('images/myLogo.png') }}" alt="Logo" height="40">
            </a>
            <span class="navbar-text">
                Order Tracking
            </span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container my-4">
        <!-- Order Header -->
        <div class="order-details-card">
            <div class="order-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">Order #{{ $order->id }}</h4>
                        <p class="text-muted mb-0">
                            Placed on {{ $order->created_at->format('F j, Y, g:i a') }}
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        @include('components.order-status-badge', ['status' => $order->status])
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="p-3">
                <h5 class="mb-3">Order Items</h5>
                @foreach($order->items as $item)
                    <div class="order-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                @if($item->product && $item->product->images->isNotEmpty())
                                    <img src="{{ asset('storage/' . $item->product->images->first()->image_url) }}" 
                                         alt="{{ $item->product->name }}" 
                                         class="product-image">
                                @else
                                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col">
                                <h6 class="mb-1">{{ $item->product->name ?? 'Product Not Available' }}</h6>
                                @if($item->variant)
                                    <small class="text-muted">Variant: {{ $item->variant->name }}</small>
                                @endif
                            </div>
                            <div class="col-auto text-end">
                                <div class="mb-1">${{ number_format($item->price, 2) }} × {{ $item->quantity }}</div>
                                <div class="fw-bold">${{ number_format($item->price * $item->quantity, 2) }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Order Total -->
                <div class="border-top pt-3 mt-3">
                    <div class="row">
                        <div class="col text-end">
                            <div class="mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="ms-2">${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</span>
                            </div>
                            @if($order->shipping_cost > 0)
                                <div class="mb-2">
                                    <span class="text-muted">Shipping:</span>
                                    <span class="ms-2">${{ number_format($order->shipping_cost, 2) }}</span>
                                </div>
                            @endif
                            <div class="h5 mb-0">
                                <span class="text-muted">Total:</span>
                                <span class="ms-2">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delivery Information -->
        <div class="order-details-card">
            <div class="p-3">
                <h5 class="mb-3">Delivery Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Delivery Address:</strong></p>
                        <p class="text-muted">{{ $order->shipping_address }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Delivery Details:</strong></p>
                        <p class="text-muted mb-1">
                            Date: {{ $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') : 'Not specified' }}
                        </p>
                        <p class="text-muted mb-1">
                            Time: {{ $order->formatted_delivery_time ?? 'Not specified' }}
                        </p>
                        <p class="text-muted">
                            Preference: {{ ucfirst($order->delivery_preference ?? 'standard') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="order-details-card">
            <div class="p-3">
                <h5 class="mb-3">Order Timeline</h5>
                <div class="timeline">
                    <div class="timeline-item {{ in_array($order->status, ['pending', 'approved', 'packed', 'shipped', 'delivered']) ? 'active' : '' }}">
                        <strong>Order Placed</strong>
                        <p class="text-muted mb-0">{{ $order->created_at->format('F j, Y, g:i a') }}</p>
                    </div>
                    
                    @if(in_array($order->status, ['approved', 'packed', 'shipped', 'delivered']))
                        <div class="timeline-item active">
                            <strong>Order Approved</strong>
                            <p class="text-muted mb-0">
                                {{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('F j, Y, g:i a') : 'Processing' }}
                            </p>
                        </div>
                    @endif
                    
                    @if(in_array($order->status, ['packed', 'shipped', 'delivered']))
                        <div class="timeline-item active">
                            <strong>Order Packed</strong>
                            <p class="text-muted mb-0">Ready for shipment</p>
                        </div>
                    @endif
                    
                    @if(in_array($order->status, ['shipped', 'delivered']))
                        <div class="timeline-item active">
                            <strong>Order Shipped</strong>
                            <p class="text-muted mb-0">On the way to you</p>
                        </div>
                    @endif
                    
                    @if($order->status === 'delivered')
                        <div class="timeline-item active">
                            <strong>Order Delivered</strong>
                            <p class="text-muted mb-0">
                                {{ $order->delivered_at ? \Carbon\Carbon::parse($order->delivered_at)->format('F j, Y, g:i a') : 'Completed' }}
                            </p>
                        </div>
                    @endif
                    
                    @if($order->status === 'rejected')
                        <div class="timeline-item active text-danger">
                            <strong>Order Rejected</strong>
                            <p class="text-muted mb-0">Please contact support for more information</p>
                        </div>
                    @endif
                    
                    @if($order->status === 'cancelled')
                        <div class="timeline-item active text-warning">
                            <strong>Order Cancelled</strong>
                            <p class="text-muted mb-0">This order has been cancelled</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Login Prompt -->
        <div class="login-prompt">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-1">Want to manage your orders?</h6>
                    <p class="mb-0 text-muted">Login to your account to view all orders, repeat orders, and more.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('login') }}" class="btn btn-primary">Login to Account</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>