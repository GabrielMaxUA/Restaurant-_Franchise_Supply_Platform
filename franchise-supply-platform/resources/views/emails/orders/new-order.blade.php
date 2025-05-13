@component('mail::message')
# New Order Notification

**Hello {{ $recipientType }} Team,**

A new order has been placed and requires your attention.

## Order Information
**Order #:** {{ $order->id }}  
**Franchisee:** {{ $franchiseeName }}  
**Date:** {{ $formattedDate }}  
**Total Amount:** {{ $formattedTotal }}

## Delivery Information
**Address:** {{ $order->shipping_address }}  
**City:** {{ $order->shipping_city }}  
**State:** {{ $order->shipping_state }}  
**ZIP:** {{ $order->shipping_zip }}  
**Requested Delivery Date:** {{ $deliveryDate }}  
@if($isExpressDelivery)
**EXPRESS DELIVERY REQUESTED**
@endif

@if($order->contact_phone)
**Contact Phone:** {{ $order->contact_phone }}
@endif

## Order Summary
This order contains {{ $itemCount }} item(s).

@component('mail::table')
| Product | Quantity | Price | Total |
|---------|----------|-------|-------|
@foreach($order->items as $item)
| {{ $item->product->name ?? 'Product Not Available' }} @if($item->variant) ({{ $item->variant->name }}) @endif | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} | ${{ number_format($item->price * $item->quantity, 2) }} |
@endforeach
@endcomponent

@component('mail::button', ['url' => $actionUrl, 'color' => 'primary'])
{{ $actionText }}
@endcomponent

Thank you for your prompt attention to this order.

Regards,<br>
{{ config('app.name') }}

---
*This is an automated message from your Restaurant Franchise Supply Platform. Please do not reply to this email.*
@endcomponent