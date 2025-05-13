<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'status',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that the notification belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the notification.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Scope a query to only include unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Mark the notification as read.
     */
    public function markAsRead()
    {
        $this->is_read = true;
        $this->save();

        return $this;
    }

    /**
     * Create a new notification for order placed.
     *
     * @param  \App\Models\Order  $order
     * @return self
     */
    public static function createOrderPlacedNotification(Order $order)
    {
        return self::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'status' => 'Order placed',
            'is_read' => false,
        ]);
    }

    /**
     * Create a new notification for various users.
     *
     * @param  \App\Models\Order  $order
     * @param  array  $userIds
     * @param  string  $status
     * @return array
     */
    public static function createForUsers(Order $order, array $userIds, string $status = 'New order')
    {
        $notifications = [];

        foreach ($userIds as $userId) {
            $notifications[] = self::create([
                'user_id' => $userId,
                'order_id' => $order->id,
                'status' => $status,
                'is_read' => false,
            ]);
        }

        return $notifications;
    }

    /**
     * Get status icon class based on order status.
     */
    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'pending' => 'fas fa-clock text-secondary',
            'approved' => 'fas fa-check text-success',
            'rejected' => 'fas fa-times text-danger',
            'packed' => 'fas fa-box text-primary',
            'shipped' => 'fas fa-truck text-info',
            'delivered' => 'fas fa-check-circle text-success',
            'cancelled' => 'fas fa-ban text-danger',
            default => 'fas fa-bell text-primary',
        };
    }

    /**
     * Get formatted status text.
     */
    public function getFormattedStatusAttribute()
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}