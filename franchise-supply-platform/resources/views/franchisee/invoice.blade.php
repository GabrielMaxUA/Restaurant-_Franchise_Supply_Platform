<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .company-info {
            max-width: 400px;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 15px;
        }
        .invoice-subtitle {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .address-block {
            margin-top: 20px;
        }
        .customer-info {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
        }
        .bill-to, .ship-to {
            width: 48%;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 5px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 300px;
            margin-left: auto;
            margin-bottom: 30px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .totals-label {
            font-weight: bold;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #dee2e6;
            padding-top: 5px;
            margin-top: 5px;
        }
        .notes {
            margin-top: 40px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="invoice-title">INVOICE</div>
                <div>{{ $companyInfo['name'] }}</div>
                <div>{{ $companyInfo['address'] }}</div>
                <div>{{ $companyInfo['city'] }}, {{ $companyInfo['state'] }} {{ $companyInfo['zip'] }}</div>
                <div>{{ $companyInfo['phone'] }}</div>
                <div>{{ $companyInfo['email'] }}</div>
                <div>{{ $companyInfo['website'] }}</div>
            </div>
            <div class="invoice-details">
                <div class="invoice-subtitle">INVOICE #{{ $order->id }}</div>
                <div>Date: {{ \Carbon\Carbon::parse($order->created_at)->format('F d, Y') }}</div>
                @if($order->status == 'approved')
                <div>Approved: {{ \Carbon\Carbon::parse($order->updated_at)->format('F d, Y') }}</div>
                @endif
                @if($order->status == 'delivered' && isset($order->delivered_at))
                <div>Delivered: {{ \Carbon\Carbon::parse($order->delivered_at)->format('F d, Y') }}</div>
                @endif
                <div>Status: {{ ucfirst($order->status) }}</div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="bill-to">
                <div class="section-title">BILL TO</div>
                <div>{{ $order->user->franchiseeProfile->company_name }}</div>
                <div>{{ $order->user->franchiseeProfile->contact_name }}</div>
                <div>{{ $order->user->email }}</div>
                <div>{{ $order->user->franchiseeProfile->phone_number }}</div>
            </div>
            <div class="ship-to">
                <div class="section-title">SHIP TO</div>
                <div>{{ $order->user->franchiseeProfile->company_name }}</div>
                <div>{{ $order->shipping_address }}</div>
            </div>
        </div>

        <!-- Order Items -->
        <table class="table">
            <thead>
                <tr>
                    <th width="10%">QTY</th>
                    <th width="45%">DESCRIPTION</th>
                    <th width="20%">UNIT PRICE</th>
                    <th width="25%" class="text-right">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->quantity }}</td>
                    <td>
                        {{ $item->product->name ?? 'Product Not Available' }}
                        @if($item->variant)
                        <br><small>{{ $item->variant->name }}</small>
                        @endif
                    </td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row">
                <div class="totals-label">Subtotal:</div>
                <div>${{ number_format($order->total_amount - ($order->shipping_cost ?? 0), 2) }}</div>
            </div>
            @if(isset($order->shipping_cost) && $order->shipping_cost > 0)
            <div class="totals-row">
                <div class="totals-label">Shipping:</div>
                <div>${{ number_format($order->shipping_cost, 2) }}</div>
            </div>
            @endif
            <div class="totals-row grand-total">
                <div class="totals-label">TOTAL:</div>
                <div>${{ number_format($order->total_amount, 2) }}</div>
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="notes">
            <div class="section-title">NOTES</div>
            <div>{{ $order->notes }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>Invoice generated on {{ date('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html>