<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            @page {
                size: letter;
                margin: 0.5in;
            }
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            background-color: #f9f9f9;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #4e73df;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .print-button:hover {
            background-color: #2e59d9;
        }
        
        .invoice-container {
            max-width: 850px;
            margin: 40px auto;
            padding: 40px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            background-color: white;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 20px;
        }
        
        .company-info {
            max-width: 400px;
        }
        
        .company-name {
            font-size: 22px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .company-details {
            font-size: 14px;
            color: #555;
            line-height: 1.6;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .invoice-subtitle {
            font-size: 18px;
            margin-bottom: 8px;
            color: #444;
        }
        
        .invoice-info {
            font-size: 14px;
            color: #555;
            margin-bottom: 6px;
        }
        
        .invoice-info-label {
            font-weight: bold;
            min-width: 100px;
            display: inline-block;
        }
        
        .invoice-dates {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        
        .customer-info {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .bill-to, .ship-to {
            width: 48%;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid #4e73df;
            padding-bottom: 5px;
            color: #4e73df;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table-container {
            margin-bottom: 30px;
            border-radius: 5px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .table th {
            background-color: #4e73df;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .table tr:hover {
            background-color: #f1f5ff;
        }
        
        .text-right {
            text-align: right;
        }
        
        .totals {
            width: 350px;
            margin-left: auto;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .totals-row:last-child {
            border-bottom: none;
        }
        
        .totals-label {
            font-weight: bold;
            color: #555;
        }
        
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            background-color: #4e73df;
            color: white;
        }
        
        .notes {
            margin-top: 40px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #4e73df;
        }
        
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .payment-info {
            margin: 30px 0;
            background-color: #f0f4ff;
            padding: 15px;
            border-radius: 5px;
        }
        
        .payment-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #4e73df;
        }
        
        .thank-you {
            font-size: 18px;
            color: #4e73df;
            text-align: center;
            margin: 30px 0;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-approved {
            background-color: #1cc88a;
            color: white;
        }
        
        .status-packed {
            background-color: #36b9cc;
            color: white;
        }
        
        .status-shipped {
            background-color: #4e73df;
            color: white;
        }
        
        .status-delivered {
            background-color: #1cc88a;
            color: white;
        }
        
        .company-logo {
            max-height: 70px;
            margin-bottom: 15px;
            display: block;
        }
        
        .express-delivery {
            background-color: #e74a3b;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">Print Invoice</button>
    
    <div class="invoice-container">
        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                @if($adminDetail && $adminDetail->logo_path && file_exists(public_path('storage/' . $adminDetail->logo_path)))
                    <img src="{{ asset('storage/' . $adminDetail->logo_path) }}" alt="Company Logo" class="company-logo">
                @elseif(file_exists(public_path('images/myLogo.png')))
                    <img src="{{ asset('images/myLogo.png') }}" alt="Company Logo" class="company-logo">
                @endif
                
                <div class="company-name">
                    {{ $adminDetail->company_name ?? config('company.name', 'Restaurant Franchise Supply') }}
                </div>
                
                <div class="company-details">
                    {{ $adminDetail->address ?? config('company.address', '478 Mortimer Ave') }}<br>
                    {{ $adminDetail->city ?? config('company.city', 'New York') }}, 
                    {{ $adminDetail->state ?? config('company.state', 'NY') }} 
                    {{ $adminDetail->postal_code ?? config('company.zip', '10022') }}<br>
                    Phone: {{ $adminDetail->phone ?? config('company.phone', '(555) 123-4567') }}<br>
                    Email: {{ $adminDetail->email ?? config('company.email', 'support@restaurantfranchisesupply.com') }}<br>
                    {{ $adminDetail->website ?? config('company.website', 'www.restaurantfranchisesupply.com') }}
                </div>
            </div>
            
            <div class="invoice-details">
                <div class="invoice-title">INVOICE</div>
                
                <div class="invoice-info">
                    <span class="invoice-info-label">Invoice #:</span> {{ $invoiceNumber }}
                </div>
                
                <div class="invoice-info">
                    <span class="invoice-info-label">Order #:</span> {{ $order->id }}
                </div>
                
                <div class="invoice-info">
                    <span class="invoice-info-label">Status:</span> 
                    <span class="status-badge status-{{ strtolower($order->status) }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                
                <div class="invoice-dates">
                    <div class="invoice-info">
                        <span class="invoice-info-label">Issue Date:</span> {{ $currentDate }}
                    </div>
                    
                    <div class="invoice-info">
                        <span class="invoice-info-label">Due Date:</span> {{ $dueDate }}
                    </div>
                    
                    @if($order->status == 'approved' && isset($order->approved_at))
                    <div class="invoice-info">
                        <span class="invoice-info-label">Approved:</span> {{ \Carbon\Carbon::parse($order->approved_at)->format('F d, Y') }}
                    </div>
                    @endif
                    
                    @if($order->status == 'delivered' && isset($order->delivered_at))
                    <div class="invoice-info">
                        <span class="invoice-info-label">Delivered:</span> {{ \Carbon\Carbon::parse($order->delivered_at)->format('F d, Y') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="customer-info">
            <div class="bill-to">
                <div class="section-title">BILL TO</div>
                @if($order->user->franchiseeProfile)
                    <div style="font-weight: bold; font-size: 16px; margin-bottom: 8px;">
                        {{ $order->user->franchiseeProfile->company_name }}
                    </div>
                    
                    @if($order->user->franchiseeProfile->company_logo)
                        <div style="margin: 10px 0;">
                            <img src="{{ asset('storage/' . $order->user->franchiseeProfile->company_logo) }}" 
                                 alt="Franchisee Logo" style="max-height: 40px; max-width: 120px;">
                        </div>
                    @endif
                    
                    <div>{{ $order->user->franchiseeProfile->contact_name ?? $order->user->username }}</div>
                    <div>{{ $order->user->email }}</div>
                    <div>{{ $order->user->franchiseeProfile->phone_number ?? $order->user->phone ?? 'N/A' }}</div>
                    
                    @if($order->user->franchiseeProfile->address)
                        <div style="margin-top: 8px;">{{ $order->user->franchiseeProfile->address }}</div>
                    @endif
                @else
                    <div style="font-weight: bold; font-size: 16px; margin-bottom: 8px;">
                        {{ $order->user->username }}
                    </div>
                    <div>{{ $order->user->email }}</div>
                    <div>{{ $order->user->phone ?? 'N/A' }}</div>
                @endif
            </div>
            
            <div class="ship-to">
                <div class="section-title">SHIP TO</div>
                <div style="font-weight: bold; font-size: 16px; margin-bottom: 8px;">
                    {{ $order->user->franchiseeProfile->company_name ?? $order->user->username }}
                </div>
                
                <div>{{ $order->shipping_address }}</div>
                <div>{{ $order->shipping_city ?? '' }}, {{ $order->shipping_state ?? '' }} {{ $order->shipping_zip ?? '' }}</div>
                
                @if($order->contact_phone)
                    <div style="margin-top: 8px;">Contact: {{ $order->contact_phone }}</div>
                @endif
                
                @if(isset($order->delivery_date))
                    <div style="margin-top: 8px;">Delivery Date: {{ \Carbon\Carbon::parse($order->delivery_date)->format('F d, Y') }}</div>
                @endif
                
                @if(isset($order->delivery_preference) && $order->delivery_preference == 'express')
                    <div class="express-delivery">EXPRESS DELIVERY</div>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="table-container">
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
                            <br><small style="color: #666;">{{ $item->variant->name }}</small>
                            @endif
                        </td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td class="text-right">${{ number_format($item->price * $item->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

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

        <!-- Payment Info -->
        <div class="payment-info">
            <div class="payment-title">PAYMENT INFORMATION</div>
            <div>Please include the invoice number <strong>{{ $invoiceNumber }}</strong> with your payment.</div>
            <div>Payment Terms: Net 30</div>
            @if($adminDetail && $adminDetail->company_name)
                <div>Make checks payable to: {{ $adminDetail->company_name }}</div>
            @else
                <div>Make checks payable to: {{ config('company.name', 'Restaurant Franchise Supply') }}</div>
            @endif
        </div>
        
        <!-- Notes -->
        @if($order->notes)
        <div class="notes">
            <div class="section-title">NOTES</div>
            <div style="white-space: pre-line;">{{ $order->notes }}</div>
        </div>
        @endif
        
        <div class="thank-you">
            Thank You for Your Business!
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>{{ config('company.invoice_footer_text', 'Thank you for your business!') }}</p>
            <p>Invoice #{{ $invoiceNumber }} | Generated on {{ date('Y-m-d H:i:s') }}</p>
            @if($adminDetail && $adminDetail->tax_id)
                <p>Tax ID: {{ $adminDetail->tax_id }}</p>
            @elseif(config('company.tax_id'))
                <p>Tax ID: {{ config('company.tax_id') }}</p>
            @endif
        </div>
    </div>
    
    <script>
        // Auto-print when page loads
        window.onload = function() {
            // Wait 1 second for styles to load
            setTimeout(function() {
                // Only auto-print if the URL contains a print parameter
                if (window.location.search.includes('print=true')) {
                    window.print();
                }
            }, 1000);
        };
    </script>
</body>
</html>