<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Status Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #ffffff;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #eee;
        }
        .status-update {
            color: {{ $statusColor }};
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .order-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .order-details h3 {
            margin-top: 0;
            color: #333;
        }
        .detail-row {
            margin: 10px 0;
        }
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        .cta-section {
            text-align: center;
            margin: 30px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: {{ $statusColor }};
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .cta-button:hover {
            opacity: 0.9;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
        .company-info {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="status-update">Order Status Update</h1>
        </div>
        
        <div class="content">
            <p>Hello {{ $recipientName }},</p>
            
            <p>{{ $statusMessage }}</p>
            
            <div class="order-details">
                <h3>Order Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Order ID:</span> #{{ $order->id }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span> 
                    <span style="color: {{ $statusColor }}; font-weight: bold;">{{ ucfirst($order->status) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span> ${{ number_format($order->total_amount, 2) }}
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span> {{ $order->created_at->format('F j, Y, g:i a') }}
                </div>
                @if($order->delivery_date)
                <div class="detail-row">
                    <span class="detail-label">Delivery Date:</span> {{ \Carbon\Carbon::parse($order->delivery_date)->format('F j, Y') }}
                </div>
                @endif
            </div>
            
            <div class="cta-section">
                <p>View your complete order details and track its progress:</p>
                <a href="{{ $trackingUrl }}" class="cta-button">View Order Details</a>
            </div>
            
            <p>If you have any questions about your order, please don't hesitate to contact our customer service team.</p>
            
            <div class="company-info">
                <p>Best regards,<br>
                {{ config('mail.from.name', 'Restaurant Franchise Supply') }} Team</p>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated message. Please do not reply to this email.</p>
            <p>If you're having trouble with the button above, copy and paste this URL into your browser:<br>
            <a href="{{ $trackingUrl }}" style="color: #666;">{{ $trackingUrl }}</a></p>
        </div>
    </div>
</body>
</html>