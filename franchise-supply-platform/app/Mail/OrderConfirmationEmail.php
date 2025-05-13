<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address', 'orders@restaurantfranchisesupply.com'),
                config('mail.from.name', 'Restaurant Franchise Supply')
            ),
            subject: 'Order Confirmation - Order #' . $this->order->id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Format customer name
        $recipientName = $this->order->user->franchiseeProfile->contact_name ?? $this->order->user->username;
        $formattedDate = $this->order->created_at->format('F j, Y, g:i a');
        $formattedTotal = '$' . number_format($this->order->total_amount, 2);
        
        // Load delivery info
        $deliveryDate = $this->order->delivery_date 
            ? \Carbon\Carbon::parse($this->order->delivery_date)->format('F j, Y') 
            : 'Not specified';
            
        $isExpressDelivery = $this->order->delivery_preference == 'express';
        
        // Format delivery time
        $deliveryTimeMap = [
            'morning' => 'Morning (8:00 AM - 12:00 PM)',
            'afternoon' => 'Afternoon (12:00 PM - 4:00 PM)',
            'evening' => 'Evening (4:00 PM - 8:00 PM)',
        ];
        $deliveryTime = $deliveryTimeMap[$this->order->delivery_time] ?? $this->order->delivery_time ?? 'Not specified';
        
        // Calculate order subtotal and shipping
        $subtotal = $this->order->total_amount - ($this->order->shipping_cost ?? 0);
        $shippingCost = $this->order->shipping_cost ?? 0;
        
        // Generate tracking URL
        $trackingUrl = url('/franchisee/orders/' . $this->order->id . '/details');

        return new Content(
            markdown: 'emails.orders.order-confirmation',
            with: [
                'order' => $this->order,
                'recipientName' => $recipientName,
                'formattedDate' => $formattedDate,
                'formattedTotal' => $formattedTotal,
                'deliveryDate' => $deliveryDate,
                'deliveryTime' => $deliveryTime,
                'isExpressDelivery' => $isExpressDelivery,
                'subtotal' => $subtotal,
                'shippingCost' => $shippingCost,
                'trackingUrl' => $trackingUrl
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