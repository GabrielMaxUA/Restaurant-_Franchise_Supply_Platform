@extends('layouts.' . (auth()->user()->isAdmin() ? 'admin' : (auth()->user()->isWarehouse() ? 'warehouse' : 'franchisee')))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Notifications</h1>
        
        @if(auth()->user()->unreadNotifications()->count() > 0)
        <a href="{{ route('notifications.mark-all-read') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-check-double me-1"></i> Mark all as read
        </a>
        @endif
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Your Notifications</h6>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($notifications as $notification)
                                <tr class="{{ $notification->is_read ? '' : 'table-light' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <span class="notification-icon me-2">
                                                <i class="{{ $notification->status_icon }}"></i>
                                            </span>
                                            {{ ucfirst($notification->status) }}
                                        </div>
                                    </td>
                                    <td>Order #{{ $notification->order->id ?? 'Unknown' }} - {{ $notification->formatted_status }}</td>
                                    <td>
                                        @if($notification->order)
                                            <a href="{{ auth()->user()->isAdmin()
                                                ? route('admin.orders.show', $notification->order->id)
                                                : (auth()->user()->isWarehouse()
                                                    ? route('warehouse.orders.show', $notification->order->id)
                                                    : route('franchisee.orders.details', $notification->order->id))
                                            }}">
                                                Order #{{ $notification->order->id }}
                                            </a>
                                        @else
                                            <span class="text-muted">Order not found</span>
                                        @endif
                                    </td>
                                    <td>{{ $notification->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex">
                                            @if(!$notification->is_read)
                                                <a href="{{ route('notifications.mark-read', $notification->id) }}" class="btn btn-sm btn-outline-primary me-2" title="Mark as read">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            @endif
                                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete notification">
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
                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-bell-slash fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">No notifications</h4>
                    <p class="text-muted">You don't have any notifications at the moment.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete confirmation
        const deleteForms = document.querySelectorAll('.delete-form');
        
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this notification?')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush
@endsection