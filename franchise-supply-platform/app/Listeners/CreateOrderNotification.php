<?php

namespace App\Listeners;

use App\Events\OrderSaved;
use App\Models\OrderNotification;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateOrderNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(
        private PushNotificationService $pushNotificationService
    ) {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderSaved $event): void
    {
        $order = $event->order;
        
        // If this is a new order, notify all admins and warehouse staff
        if (!$event->statusChanged && $order->wasRecentlyCreated) {
            $this->notifyStaffAboutNewOrder($order);
            return;
        }
        
        // If the status changed, create notifications for the franchisee owner
        // and for relevant staff members
        if ($event->statusChanged) {
            // Notify the franchisee about the status change
            $this->createNotificationForOwner($order, $event->oldStatus);
            
            // Send push notification to franchisee
            $this->pushNotificationService->sendOrderStatusNotification($order, $event->oldStatus);
            
            // Notify staff based on the new status
            $this->notifyStaffAboutStatusChange($order, $event->oldStatus);
        }
    }
    
    /**
     * Create a notification for the order owner (franchisee).
     */
    private function createNotificationForOwner($order, $oldStatus)
    {
        // Check if there's already a notification for this order and status
        $existing = OrderNotification::where('user_id', $order->user_id)
            ->where('order_id', $order->id)
            ->where('status', $order->status)
            ->exists();

        // Only create if no duplicate exists
        if (!$existing) {
            OrderNotification::create([
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'status' => $order->status,
                'is_read' => false
            ]);
        }
    }
    
    /**
     * Notify staff members about a new order.
     */
    private function notifyStaffAboutNewOrder($order)
    {
        // Get all admin and warehouse staff users
        $staffUsers = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['admin', 'warehouse']);
        })->get();
        
        foreach ($staffUsers as $user) {
            // Check for existing notification
            $exists = OrderNotification::where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->where('status', 'pending')
                ->exists();

            if (!$exists) {
                OrderNotification::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'status' => 'pending',
                    'is_read' => false
                ]);
            }
        }
    }
    
    /**
     * Notify relevant staff members about status changes.
     */
    private function notifyStaffAboutStatusChange($order, $oldStatus)
    {
        $userRolesToNotify = $this->getUserRolesToNotify($order->status);
        
        if (empty($userRolesToNotify)) {
            return;
        }
        
        // Get staff users with relevant roles
        $staffUsers = User::whereHas('role', function ($query) use ($userRolesToNotify) {
            $query->whereIn('name', $userRolesToNotify);
        })->get();
        
        foreach ($staffUsers as $user) {
            // Check for existing notification
            $exists = OrderNotification::where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->where('status', $order->status)
                ->exists();

            if (!$exists) {
                OrderNotification::create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'is_read' => false
                ]);
            }
        }
    }
    
    /**
     * Determine which roles should be notified based on the status.
     */
    private function getUserRolesToNotify($status)
    {
        switch ($status) {
            case 'pending':
                return ['admin']; // New orders are for admins to approve
            case 'approved':
                return ['warehouse']; // Approved orders are for warehouse to fulfill
            case 'rejected':
            case 'cancelled':
                return []; // No staff notifications needed for these statuses
            case 'packed':
            case 'shipped':
            case 'delivered':
                return ['admin']; // Keep admins in the loop on fulfillment
            default:
                return ['admin', 'warehouse']; // Default to notifying everyone
        }
    }
    
    /**
     * Get a human-readable message for the status change.
     */
    private function getStatusChangeMessage($newStatus, $oldStatus, $isForCustomer)
    {
        if ($isForCustomer) {
            // Messages for the order owner (franchisee)
            switch ($newStatus) {
                case 'approved':
                    return "Your order has been approved and is now being processed.";
                case 'rejected':
                    return "Your order has been rejected. Please contact support for more information.";
                case 'packed':
                    return "Your order has been packed and is being prepared for shipping.";
                case 'shipped':
                    return "Your order has been shipped and is on its way to you.";
                case 'delivered':
                    return "Your order has been delivered. Thank you for your business!";
                case 'cancelled':
                    return "Your order has been cancelled.";
                default:
                    return "Your order status has been updated to: " . ucfirst($newStatus);
            }
        } else {
            // Messages for staff members
            switch ($newStatus) {
                case 'approved':
                    return "Order #" . $order->id . " has been approved and is ready for fulfillment.";
                case 'rejected':
                    return "Order #" . $order->id . " has been rejected by an administrator.";
                case 'packed':
                    return "Order #" . $order->id . " has been packed and is ready for shipping.";
                case 'shipped':
                    return "Order #" . $order->id . " has been shipped to the customer.";
                case 'delivered':
                    return "Order #" . $order->id . " has been marked as delivered.";
                case 'cancelled':
                    return "Order #" . $order->id . " has been cancelled.";
                default:
                    return "Order #" . $order->id . " status has been updated to: " . ucfirst($newStatus);
            }
        }
    }
}