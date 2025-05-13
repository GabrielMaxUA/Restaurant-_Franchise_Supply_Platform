<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Packing Slip - Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24pt;
            margin: 0;
            padding: 0;
        }
        .order-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .address-box {
            border: 1px solid #ccc;
            padding: 15px;
            width: 45%;
        }
        .order-details {
            width: 45%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .totals {
            text-align: right;
            margin-top: 20px;
        }
        .signature {
            margin-top: 50px;
            border-top: 1px solid #000;
            display: inline-block;
            padding-top: 5px;
            margin-right: 50px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10pt;
            color: #666;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .print-button {
                display: none;
            }
        }
        .print-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .notes-box {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print Packing Slip</button>
    
    <div class="header">
        <img src="{{ asset('images/myLogo.png') }}" alt="Company Logo" class="logo">
        <h1>PACKING SLIP</h1>
        <p>Order #{{ $order->id }}</p>
    </div>
    
    <div class="order-info">
        <div class="address-box">
            <h3>Ship To:</h3>
            <p>
                <strong>{{ $order->user->username }}</strong><br>
                {{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : '' }}<br>
                {{ $order->shipping_address }}<br>
                {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>
                Phone: {{ $order->contact_phone ?? ($order->user->phone ?? 'N/A') }}
            </p>
        </div>
        
        <div class="order-details">
            <h3>Order Information:</h3>
            <p>
                <strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}<br>
                <strong>Order Status:</strong> {{ ucfirst($order->status) }}<br>
                @if($order->purchase_order)
                    <strong>Purchase Order #:</strong> {{ $order->purchase_order }}<br>
                @endif
                <strong>Requested Delivery:</strong> {{ $order->delivery_date ? $order->delivery_date->format('M d, Y') : 'Not specified' }}<br>
                <strong>Delivery Time:</strong> 
                @if($order->delivery_time == 'morning')
                    Morning (8:00 AM - 12:00 PM)
                @elseif($order->delivery_time == 'afternoon')
                    Afternoon (12:00 PM - 4:00 PM)
                @elseif($order->delivery_time == 'evening')
                    Evening (4:00 PM - 8:00 PM)
                @else
                    {{ $order->delivery_time ?? 'Not specified' }}
                @endif
            </p>
        </div>
    </div>
    
    @if($order->notes)
        <div class="notes-box">
            <h3>Order Notes:</h3>
            <p>{{ $order->notes }}</p>
        </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>SKU</th>
                <th>Variant</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product->name ?? 'Unknown Product' }}</td>
                    <td>{{ $item->product->id ?? 'N/A' }}</td>
                    <td>{{ $item->variant->name ?? 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Subtotal:</strong></td>
                <td>${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;">Shipping:</td>
                <td>${{ number_format($order->shipping_cost ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" style="text-align: right;"><strong>Total:</strong></td>
                <td><strong>${{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div>
        <p><strong>Items to Collect: {{ $order->items->sum('quantity') }}</strong></p>
        <p>This is not a receipt. No payment information is included.</p>
    </div>
    
    <div>
        <div class="signature">
            Packed By: ___________________
        </div>
        <div class="signature">
            Date: ___________________
        </div>
    </div>
    
    <div class="footer">
        <p>Restaurant Franchise Supply Platform | support@example.com | (555) 123-4567</p>
    </div>
</body>
</html>