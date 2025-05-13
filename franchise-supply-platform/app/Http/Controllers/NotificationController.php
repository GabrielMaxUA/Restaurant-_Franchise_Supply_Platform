<?php

namespace App\Http\Controllers;

use App\Models\OrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications.
     */
    public function index()
    {
        $user = Auth::user();
        $notifications = $user->orderNotifications()
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($id)
    {
        $notification = OrderNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->markAsRead();

        // If notification is related to an order, redirect to the order details
        if ($notification->order) {
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.orders.show', $notification->order_id)
                    ->with('success', 'Notification marked as read.');
            } elseif (Auth::user()->isWarehouse()) {
                return redirect()->route('warehouse.orders.show', $notification->order_id)
                    ->with('success', 'Notification marked as read.');
            } else {
                return redirect()->route('franchisee.orders.details', $notification->order_id)
                    ->with('success', 'Notification marked as read.');
            }
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }
    
    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        OrderNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
            
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
    
    /**
     * Delete a notification.
     */
    public function destroy($id)
    {
        $notification = OrderNotification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
            
        $notification->delete();
        
        return redirect()->back()->with('success', 'Notification deleted.');
    }
    
    /**
     * Get unread notifications count for the current user.
     * Used for AJAX requests.
     */
    public function unreadCount()
    {
        $count = Auth::user()->unreadNotifications()->count();
        
        return response()->json(['count' => $count]);
    }
    
    /**
     * Get recent notifications for the current user.
     * Used for the notification dropdown.
     */
    public function recent()
    {
        $notifications = Auth::user()->orderNotifications()
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $unreadCount = Auth::user()->unreadNotifications()->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
}