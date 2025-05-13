<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewOrderNotification extends Mailable
{
    use Queueable;

    protected $order;
    protected $isAdmin;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order, bool $isAdmin = false)
    {
        $this->order = $order;
        $this->isAdmin = $isAdmin;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isAdmin
            ? 'New Order Placed (Admin Copy) - Order #' . $this->order->id
            : 'New Order Requires Processing - Order #' . $this->order->id;

        return new Envelope(
            from: new Address(
                config('mail.from.address', 'orders@restaurantfranchisesupply.com'),
                config('mail.from.name', 'Restaurant Franchise Supply')
            ),
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Load customer info
        $franchiseeName = $this->order->user->franchiseeProfile->company_name ?? $this->order->user->username;
        $formattedDate = $this->order->created_at->format('F j, Y, g:i a');
        $formattedTotal = '$' . number_format($this->order->total_amount, 2);

        // Load delivery info
        $deliveryDate = $this->order->delivery_date
            ? \Carbon\Carbon::parse($this->order->delivery_date)->format('F j, Y')
            : 'Not specified';

        $isExpressDelivery = $this->order->delivery_preference == 'express';

        // Order summary
        $itemCount = $this->order->items->sum('quantity');

        // Action URL
        $actionUrl = $this->isAdmin
            ? url('/admin/orders/' . $this->order->id)
            : url('/warehouse/orders/' . $this->order->id);

        $actionText = $this->isAdmin ? 'View Order Details (Admin)' : 'Process Order';

        // Get receiver type for greeting
        $recipientType = $this->isAdmin ? 'Admin' : 'Warehouse';

        return new Content(
            markdown: 'emails.orders.new-order',
            with: [
                'order' => $this->order,
                'isAdmin' => $this->isAdmin,
                'franchiseeName' => $franchiseeName,
                'formattedDate' => $formattedDate,
                'formattedTotal' => $formattedTotal,
                'deliveryDate' => $deliveryDate,
                'isExpressDelivery' => $isExpressDelivery,
                'itemCount' => $itemCount,
                'actionUrl' => $actionUrl,
                'actionText' => $actionText,
                'recipientType' => $recipientType
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}