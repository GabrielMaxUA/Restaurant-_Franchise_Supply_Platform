<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <title>New Order #{{ $order->id }} - {{ $recipientType }} Notification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <style>
        body {
            background-color: #f1f3f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 620px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 24px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 32px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 16px;
            font-weight: 500;
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 24px 0;
        }
        .order-details h3 {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 18px;
            color: #343a40;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 500;
            color: #495057;
        }
        .detail-value {
            text-align: right;
            color: #212529;
        }
        .express-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 12px;
            border-radius: 4px;
            margin: 16px 0;
            font-weight: 500;
            text-align: center;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            background-color: #dc3545;
            color: #fff;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #c82333;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th,
        .items-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>New Order #{{ $order->id }}</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello {{ $recipientType }} Team,</p>

            <p>A new order has been placed and requires your attention.</p>

            <!-- Order Details -->
            <div class="order-details">
                <h3>Order Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Order #:</span>
                    <span class="detail-value">#{{ $order->id }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Franchisee:</span>
                    <span class="detail-value">{{ $franchiseeName }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value">{{ $formattedDate }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">{{ $formattedTotal }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Items Count:</span>
                    <span class="detail-value">{{ $itemCount }} item(s)</span>
                </div>
            </div>

            <!-- Delivery Information -->
            <div class="order-details">
                <h3>Delivery Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value">{{ $order->shipping_address }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">City:</span>
                    <span class="detail-value">{{ $order->shipping_city }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">State:</span>
                    <span class="detail-value">{{ $order->shipping_state }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">ZIP:</span>
                    <span class="detail-value">{{ $order->shipping_zip }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Requested Delivery Date:</span>
                    <span class="detail-value">{{ $deliveryDate }}</span>
                </div>
                @if($order->contact_phone)
                <div class="detail-row">
                    <span class="detail-label">Contact Phone:</span>
                    <span class="detail-value">{{ $order->contact_phone }}</span>
                </div>
                @endif
            </div>

            @if($isExpressDelivery)
            <div class="express-notice">
                ⚡ EXPRESS DELIVERY REQUESTED ⚡
            </div>
            @endif

            <p>To view the order details and manage the order, please click the link below:</p>

            <!-- Action Link -->
            <div class="button-container">
                <a href="{{ $actionUrl }}" class="button" style="display: inline-block;">
                    {{ $actionText }}
                </a>
            </div>
            
            <p style="margin-top: 10px; font-size: 13px; color: #6c757d; text-align: center;">
                If the button above doesn't work, copy and paste this link into your browser:<br>
                <a href="{{ $actionUrl }}" style="color: #dc3545; text-decoration: underline; word-break: break-all;">{{ $actionUrl }}</a>
            </p>

            <p>Thank you for your prompt attention to this order.</p>

            <p style="margin-top: 28px; text-align: center;">
                Best regards,<br>
                <strong>{{ config('app.name') }} Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated message from your Restaurant Franchise Supply Platform.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p style="margin-top: 10px; font-size: 11px; color: #999;">Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>