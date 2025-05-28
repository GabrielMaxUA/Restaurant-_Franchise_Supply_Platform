<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoiceNumber }} - Order Approved</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            background-color: #28a745;
            color: white;
            padding: 24px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
        }
        .content {
            padding: 32px 30px;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 16px;
        }
        .message p {
            margin-bottom: 16px;
        }
        .invoice-details {
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 20px;
            margin: 24px 0;
        }
        .invoice-details h3 {
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
        .attachment-note {
            background-color: #e2f0ff;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
        }
        .attachment-note p {
            margin: 0;
            font-size: 15px;
            color: #084298;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            background-color: #28a745;
            color: #fff;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 15px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #218838;
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
            <h1>Order #{{ $order->id }} Approved</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">Hello {{ $recipientName }},</p>

            <!-- Attachment Note -->
            <div class="attachment-note">
            <p>We're pleased to inform you that your order <strong>#{{ $order->id }}</strong> has been approved and is now moving forward to fulfillment.</p>
            <p>You’ll find your invoice attached to this email for your records.</p>
            </div>

            <!-- Button -->
            <div class="button-container">
                <a href="{{ $trackingUrl }}" class="button">Track Your Order</a>
            </div>

            <p>We'll notify you again once your order status updates. In the meantime, feel free to reach out if you have any questions.</p>

            <p>Thanks again for choosing us!</p>

            <p style="margin-top: 28px; text-align: center;">
                Best regards,<br>
                <strong>{{ config('company.name', 'Restaurant Franchise Supply Platform') }} Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated message. Please do not reply directly.</p>
            <p>&copy; {{ date('Y') }} {{ config('company.name', 'Restaurant Franchise Supply Platform') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
