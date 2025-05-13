<?php

namespace App\Observers;

use App\Events\OrderSaved;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "creating" event.
     */
    public function creating(Order $order): void
    {
        // Set default status if not already set
        if (!$order->status) {
            $order->status = 'pending';
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Fire the OrderSaved event for a new order
        event(new OrderSaved($order));
    }

    // Static properties to store status change information
    protected static $statusChanged = [];
    protected static $oldStatus = [];

    /**
     * Handle the Order "updating" event.
     */
    public function updating(Order $order): void
    {
        // Check if status is being changed
        $isStatusChanged = $order->isDirty('status');
        $oldStatusValue = $isStatusChanged ? $order->getOriginal('status') : null;

        // Store the values in static arrays indexed by order ID
        self::$statusChanged[$order->id] = $isStatusChanged;
        self::$oldStatus[$order->id] = $oldStatusValue;
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Get the values from static arrays
        $isStatusChanged = self::$statusChanged[$order->id] ?? false;
        $oldStatusValue = self::$oldStatus[$order->id] ?? null;

        // If status was changed, fire the OrderSaved event
        if ($isStatusChanged) {
            event(new OrderSaved($order, true, $oldStatusValue));
        }

        // Clean up static arrays to prevent memory leaks
        unset(self::$statusChanged[$order->id]);
        unset(self::$oldStatus[$order->id]);
    }
}