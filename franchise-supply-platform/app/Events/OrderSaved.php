<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderSaved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * Indicates whether the order's status was changed.
     *
     * @var bool
     */
    public $statusChanged;

    /**
     * The previous status before the change.
     *
     * @var string|null
     */
    public $oldStatus;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Order  $order
     * @param  bool  $statusChanged
     * @param  string|null  $oldStatus
     * @return void
     */
    public function __construct(Order $order, $statusChanged = false, $oldStatus = null)
    {
        $this->order = $order;
        $this->statusChanged = $statusChanged;
        $this->oldStatus = $oldStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->order->id),
        ];
    }
}