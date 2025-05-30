@component('mail::message')
# Order Confirmation - Order #{{ $order->id }}

Hello {{ $recipientName }},

Thank you for your order! We're pleased to confirm that we've received your order and it's being processed.

@component('mail::panel')
## Order Summary

**Order Number:** #{{ $order->id }}  
**Order Date:** {{ $formattedDate }}  
**Total Amount:** {{ $formattedTotal }}

**Delivery Information:**  
- **Delivery Date:** {{ $deliveryDate }}  
- **Delivery Time:** {{ $deliveryTime }}  
@if($isExpressDelivery)
- **Express Delivery:** ✓ Yes
@endif
@endcomponent


## What's Next?

Your order is currently pending approval. Once approved, we'll send you another email with your invoice and you'll be able to track your order status in real-time.

@component('mail::button', ['url' => $trackingUrl, 'color' => 'success'])
View Order Details
@endcomponent

If you have any questions about your order, please don't hesitate to contact our customer service team.

Thank you for choosing {{ config('app.name') }}!

Best regards,<br>
{{ config('app.name') }} Team

<small>
*This is an automated confirmation email. Please do not reply directly to this message.*
</small>
@endcomponent