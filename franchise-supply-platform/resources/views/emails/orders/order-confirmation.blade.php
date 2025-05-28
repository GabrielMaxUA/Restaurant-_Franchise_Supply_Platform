@component('mail::message')
# Order Confirmation

**Hello {{ $recipientName }},**

Your order has been successfully placed. Thank you for your business!

@endcomponent

If you have any questions about your order, please contact our customer service team.

Thank you for choosing {{ config('app.name') }}!

Regards,<br>
{{ config('app.name') }} Team

---
*This is an automated confirmation of your order. Please don't reply to this email.*
@endcomponent