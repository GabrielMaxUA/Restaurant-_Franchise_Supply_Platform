<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        @page {
            margin: 0.5in;
            size: A4;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; font-size: 13px; line-height: 1.6; color: #333333; background-color: #ffffff;">
    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0; padding: 0;">
        <tr>
            <td align="center" style="padding: 0;">
                <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin: 0; background-color: #ffffff;">
                    <tr>
                        <td style="padding: 20px;">
                            
                            <!-- Invoice Title Header -->
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td align="center">
                                        <div style="font-size: 36px; font-weight: bold; color: #4e73df; letter-spacing: 2px;">INVOICE</div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Header -->
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td width="60%" style="vertical-align: top;">
                                            @if(file_exists(public_path('images/invoiceLogo.png')))
                                                <img src="{{ public_path('images/invoiceLogo.png') }}" alt="Invoice Logo" style="max-width: 180px; max-height: 60px; margin-bottom: 15px;">
                                            @endif        
                                        <div style="font-size: 18px; font-weight: bold; color: #333333; margin-bottom: 10px;">{{ $adminDetail->company_name ?? 'Restaurant Franchise Supply' }}</div>
                                        <div style="font-size: 12px; color: #666666; line-height: 1.5;">
                                            {{ $adminDetail->address ?? '478 Mortimer Ave' }}<br>
                                            {{ $adminDetail->city ?? 'New York' }}, {{ $adminDetail->state ?? 'NY' }} {{ $adminDetail->postal_code ?? '10022' }}<br>
                                            Phone: {{ $adminDetail->phone ?? '(555) 123-4567' }}<br>
                                            Email: {{ $adminDetail->email ?? 'support@example.com' }}
                                        </div>
                                    </td>
                                    <td width="40%" style="vertical-align: top; text-align: right;">
                                        <div style="font-size: 12px; color: #666666; line-height: 1.8;">
                                            <strong style="color: #333333;">Invoice #:</strong> {{ $invoiceNumber }}<br>
                                            <strong style="color: #333333;">Order #:</strong> {{ $order->id }}<br>
                                            <strong style="color: #333333;">Issue Date:</strong> {{ $currentDate }}<br>
                                            <strong style="color: #333333;">Due Date:</strong> {{ $dueDate }}<br>
                                            @if($order->status == 'approved' && isset($order->approved_at))
                                                <strong style="color: #333333;">Approved:</strong> {{ \Carbon\Carbon::parse($order->approved_at)->format('F d, Y') }}<br>
                                            @endif
                                            @if($order->status == 'delivered' && isset($order->delivered_at))
                                                <strong style="color: #333333;">Delivered:</strong> {{ \Carbon\Carbon::parse($order->delivered_at)->format('F d, Y') }}<br>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Customer Info -->
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                                <tr>
                                    <td width="48%" style="vertical-align: top;">
                                        <table role="presentation" cellpadding="10" cellspacing="0" width="100%" style="border: 1px solid #cccccc; border-radius: 4px;">
                                            <tr>
                                                <td>
                                                    <div style="font-weight: bold; margin-bottom: 8px; color: #4e73df; font-size: 14px;">BILL TO</div>
                                                    <div style="font-size: 13px; line-height: 1.6; color: #333333;">
                                                        @if($order->user->franchiseeProfile)
                                                            <strong>{{ $order->user->franchiseeProfile->company_name }}</strong><br>
                                                            {{ $order->user->franchiseeProfile->contact_name ?? $order->user->username }}<br>
                                                            {{ $order->user->email }}<br>
                                                            {{ $order->user->franchiseeProfile->phone_number ?? $order->user->phone ?? 'N/A' }}<br>
                                                            {{ $order->user->franchiseeProfile->address ?? '' }}
                                                        @else
                                                            <strong>{{ $order->user->username }}</strong><br>
                                                            {{ $order->user->email }}<br>
                                                            {{ $order->user->phone ?? 'N/A' }}
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td width="4%">&nbsp;</td>
                                    <td width="48%" style="vertical-align: top;">
                                        <table role="presentation" cellpadding="10" cellspacing="0" width="100%" style="border: 1px solid #cccccc; border-radius: 4px;">
                                            <tr>
                                                <td>
                                                    <div style="font-weight: bold; margin-bottom: 8px; color: #4e73df; font-size: 14px;">SHIP TO</div>
                                                    <div style="font-size: 13px; line-height: 1.6; color: #333333;">
                                                        <strong>{{ $order->user->franchiseeProfile->company_name ?? $order->user->username }}</strong><br>
                                                        {{ $order->shipping_address }}<br>
                                                        {{ $order->shipping_city ?? '' }}, {{ $order->shipping_state ?? '' }} {{ $order->shipping_zip ?? '' }}<br>
                                                        @if($order->contact_phone)
                                                            Contact: {{ $order->contact_phone }}<br>
                                                        @endif
                                                        @if(isset($order->delivery_date))
                                                            Delivery Date: {{ \Carbon\Carbon::parse($order->delivery_date)->format('F d, Y') }}
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Order Items -->
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 20px; border-collapse: collapse;">
                                <tr>
                                    <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #cccccc; font-size: 13px; font-weight: bold; text-align: left;">QTY</th>
                                    <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #cccccc; font-size: 13px; font-weight: bold; text-align: left;">DESCRIPTION</th>
                                    <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #cccccc; font-size: 13px; font-weight: bold; text-align: left;">UNIT PRICE</th>
                                    <th style="background-color: #f0f0f0; padding: 8px; border: 1px solid #cccccc; font-size: 13px; font-weight: bold; text-align: right;">AMOUNT</th>
                                </tr>
                                @foreach($order->items as $item)
                                <tr>
                                    <td style="padding: 8px; border: 1px solid #cccccc; font-size: 13px;">{{ $item->quantity }}</td>
                                    <td style="padding: 8px; border: 1px solid #cccccc; font-size: 13px;">
                                        {{ $item->product->name ?? 'Product Not Available' }}
                                        @if($item->variant)
                                            <br><span style="font-size: 11px; color: #666666;">{{ $item->variant->name }}</span>
                                        @endif
                                    </td>
                                    <td style="padding: 8px; border: 1px solid #cccccc; font-size: 13px;">${{ number_format($item->price, 2) }}</td>
                                    <td style="padding: 8px; border: 1px solid #cccccc; font-size: 13px; text-align: right;">${{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                                @endforeach
                            </table>

                            <!-- Totals -->
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px;">
                                <tr>
                                    <td width="60%">&nbsp;</td>
                                    <td width="40%">
                                        <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; font-weight: bold; text-align: right;">Subtotal:</td>
                                                <td style="padding: 6px 0 6px 10px; font-size: 13px; text-align: right;">${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</td>
                                            </tr>
                                            @if(isset($order->shipping_cost) && $order->shipping_cost > 0)
                                            <tr>
                                                <td style="padding: 6px 0; font-size: 13px; font-weight: bold; text-align: right;">Shipping:</td>
                                                <td style="padding: 6px 0 6px 10px; font-size: 13px; text-align: right;">${{ number_format($order->shipping_cost, 2) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding: 12px 0 6px 0; font-size: 13px; font-weight: bold; text-align: right; border-top: 2px solid #4e73df;">Total:</td>
                                                <td style="padding: 12px 0 6px 10px; font-size: 13px; text-align: right; font-weight: bold; border-top: 2px solid #4e73df;">${{ number_format($order->total_amount, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Payment Info -->
                            <table role="presentation" cellpadding="12" cellspacing="0" width="100%" style="margin-top: 30px; background-color: #f2f4f8; border-radius: 5px;">
                                <tr>
                                    <td>
                                        <div style="font-size: 12px; line-height: 1.6;">
                                            <strong>Payment Terms:</strong> Net 30<br>
                                            Include invoice #: {{ $invoiceNumber }} with your payment.<br>
                                            @if($adminDetail && $adminDetail->company_name)
                                                Payable to: {{ $adminDetail->company_name }}
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Notes -->
                            @if($order->notes)
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 20px;">
                                <tr>
                                    <td style="border-left: 4px solid #4e73df; background-color: #f9f9f9; padding: 10px;">
                                        <div style="font-weight: bold; margin-bottom: 8px; color: #4e73df; font-size: 14px;">NOTES</div>
                                        <div style="font-size: 12px; color: #333333;">{{ $order->notes }}</div>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            <!-- Footer -->
                            <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <div style="font-size: 14px; font-weight: bold; color: #4e73df; margin-bottom: 10px;">Thank You for Your Business!</div>
                                        <div style="font-size: 12px; color: #555555;">Invoice #: {{ $invoiceNumber }} | Generated on {{ now()->format('Y-m-d H:i') }}</div>
                                        @if($adminDetail && $adminDetail->tax_id)
                                            <div style="font-size: 12px; color: #555555;">Tax ID: {{ $adminDetail->tax_id }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>