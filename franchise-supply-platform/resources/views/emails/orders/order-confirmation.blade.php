@component('mail::message')
# Order Confirmation

**Hello {{ $recipientName }},**

Your order has been successfully placed. Thank you for your business!

## Order Details
**Order #:** {{ $order->id }}  
**Date:** {{ $formattedDate }}  
**Total Amount:** {{ $formattedTotal }}

## Shipping Information
**Address:** {{ $order->shipping_address }}  
**City:** {{ $order->shipping_city }}  
**State:** {{ $order->shipping_state }}  
**ZIP:** {{ $order->shipping_zip }}

## Delivery Details
**Requested Delivery Date:** {{ $deliveryDate }}  
**Delivery Time:** {{ $deliveryTime }}  
@if($isExpressDelivery)
**Delivery Method:** EXPRESS DELIVERY
@else
**Delivery Method:** Standard Delivery
@endif

## Order Summary
@component('mail::table')
| Product | Quantity | Price | Total |
|---------|----------|-------|-------|
@foreach($order->items as $item)
| {{ $item->product->name ?? 'Product Not Available' }} @if($item->variant) ({{ $item->variant->name }}) @endif | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} | ${{ number_format($item->price * $item->quantity, 2) }} |
@endforeach
@endcomponent

## Order Totals
**Subtotal:** ${{ number_format($subtotal, 2) }}  
@if($shippingCost > 0)
**Shipping:** ${{ number_format($shippingCost, 2) }}  
@endif
**Total:** {{ $formattedTotal }}

@component('mail::button', ['url' => $trackingUrl, 'color' => 'primary'])
View Order Status
@endcomponent

If you have any questions about your order, please contact our customer service team.

Thank you for choosing {{ config('app.name') }}!

Regards,<br>
{{ config('app.name') }} Team

---
*This is an automated confirmation of your order. Please don't reply to this email.*
@endcomponent