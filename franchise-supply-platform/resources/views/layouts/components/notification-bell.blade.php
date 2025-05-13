<div class="dropdown me-2">
    <a class="btn btn-outline-secondary notification-btn" href="#" role="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell fa-sm"></i>
        @if(auth()->user()->unreadNotifications()->count() > 0)
            <span class="badge rounded-pill bg-danger notification-badge">
                {{ auth()->user()->unreadNotifications()->count() }}
            </span>
        @endif
    </a>
    <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
        <div class="dropdown-header d-flex justify-content-between align-items-center">
            <span>Notifications</span>
            @if(auth()->user()->unreadNotifications()->count() > 0)
                <a href="{{ route('notifications.mark-all-read') }}" class="text-decoration-none">
                    <small>Mark all as read</small>
                </a>
            @endif
        </div>
        <div class="notification-list">
            @forelse(auth()->user()->orderNotifications()->with('order')->latest()->take(5)->get() as $notification)
                <a href="{{ route('notifications.mark-read', $notification->id) }}" 
                   class="dropdown-item notification-item {{ $notification->is_read ? '' : 'unread' }}">
                    <div class="d-flex">
                        <div class="notification-icon me-3">
                            <i class="{{ $notification->status_icon }}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-text">
                                Order #{{ $notification->order->id ?? 'Unknown' }} -
                                {{ $notification->formatted_status }}
                            </div>
                            <div class="notification-time">{{ $notification->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="dropdown-item text-center">
                    No notifications
                </div>
            @endforelse
        </div>
        <div class="dropdown-footer text-center">
            <a href="{{ route('notifications.index') }}" class="text-decoration-none">View all notifications</a>
        </div>
    </div>
</div>

<!-- Styles moved to external CSS file for consistency -->