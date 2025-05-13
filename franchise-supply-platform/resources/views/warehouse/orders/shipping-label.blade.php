<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - Order #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        .container {
            width: 4in;
            height: 6in;
            margin: 20px auto;
            border: 1px solid #000;
            padding: 0.25in;
            position: relative;
        }
        .label-content {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 0.25in;
        }
        .logo {
            width: 1in;
            height: auto;
            margin-right: 0.25in;
        }
        .company-info {
            font-size: 10pt;
        }
        .ship-to {
            flex-grow: 1;
            border: 2px solid #000;
            padding: 0.25in;
            margin-bottom: 0.25in;
        }
        .ship-to h2 {
            margin-top: 0;
            margin-bottom: 0.1in;
            font-size: 14pt;
        }
        .address {
            font-size: 12pt;
            font-weight: bold;
            line-height: 1.3;
        }
        .order-info {
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
            margin-bottom: 0.1in;
        }
        .barcode {
            text-align: center;
            margin-bottom: 0.1in;
        }
        .barcode-img {
            width: 90%;
            height: auto;
        }
        .item-count {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            border: 2px solid #000;
            padding: 0.1in;
            margin-bottom: 0.1in;
        }
        .print-button {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delivery-notes {
            font-size: 10pt;
            padding: 0.1in;
            border: 1px solid #ccc;
            margin-bottom: 0.1in;
        }
        .tracking {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-top: 0.1in;
        }
        @media print {
            body {
                margin: 0;
            }
            .container {
                border: 1px dashed #ccc;
                margin: 0;
                page-break-after: always;
            }
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">Print Label</button>
    
    <div class="container">
        <div class="label-content">
            <div class="header">
                <img src="{{ asset('images/myLogo.png') }}" alt="Company Logo" class="logo">
                <div class="company-info">
                    <strong>Restaurant Franchise Supply Platform</strong><br>
                    123 Warehouse St.<br>
                    Distribution City, ST 12345
                </div>
            </div>
            
            <div class="ship-to">
                <h2>SHIP TO:</h2>
                <div class="address">
                    {{ $order->user->franchiseeProfile ? $order->user->franchiseeProfile->company_name : $order->user->username }}<br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}
                </div>
            </div>
            
            <div class="order-info">
                <div><strong>Order #:</strong> {{ $order->id }}</div>
                <div><strong>Date:</strong> {{ $order->created_at->format('m/d/Y') }}</div>
            </div>
            
            @if($order->tracking_number)
                <div class="tracking">
                    TRACKING #: {{ $order->tracking_number }}
                </div>
            @endif
            
            <div class="barcode">
                <!-- Barcode placeholder - In a real app, you would generate a real barcode -->
                <svg class="barcode-img" 
                     jsbarcode-format="CODE128"
                     jsbarcode-value="ORDER{{ $order->id }}"
                     jsbarcode-textmargin="0"
                     jsbarcode-fontoptions="bold">
                </svg>
            </div>
            
            <div class="item-count">
                ITEMS: {{ $order->items->sum('quantity') }}
            </div>
            
            @if($order->delivery_preference == 'express')
                <div style="text-align: center; font-size: 16pt; font-weight: bold; color: red; margin: 0.1in 0;">
                    EXPRESS DELIVERY
                </div>
            @endif
            
            @if($order->notes)
                <div class="delivery-notes">
                    <strong>Notes:</strong> {{ $order->notes }}
                </div>
            @endif
            
            @if($order->delivery_date)
                <div style="text-align: center; font-size: 10pt; margin-top: 0.1in;">
                    <strong>Requested Delivery:</strong> {{ $order->delivery_date->format('m/d/Y') }}
                    @if($order->delivery_time)
                        - {{ ucfirst($order->delivery_time) }}
                    @endif
                </div>
            @endif
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        JsBarcode(".barcode-img").init();
    </script>
</body>
</html>