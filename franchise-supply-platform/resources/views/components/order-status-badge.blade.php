@php
    $statusClass = match($status) {
        'pending' => 'bg-secondary',
        'approved', 'processing' => 'bg-info',
        'packed' => 'bg-warning',
        'shipped' => 'bg-primary',
        'delivered' => 'bg-success',
        'rejected', 'cancelled' => 'bg-danger',
        'out_for_delivery' => 'bg-primary',
        default => 'bg-secondary'
    };
    
    $displayText = match($status) {
        'approved' => 'Approved',
        'processing' => 'Processing',
        'out_for_delivery' => 'Out for Delivery',
        default => ucfirst($status)
    };
@endphp

<span class="badge {{ $statusClass }} {{ $class ?? '' }}">{{ $displayText }}</span>