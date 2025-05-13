<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | This file contains the company information used throughout the application,
    | including in invoices, emails, and other customer-facing documents.
    |
    */

    'name' => env('COMPANY_NAME', 'Restaurant Franchise Supply'),
    'address' => env('COMPANY_ADDRESS', '478 Mortimer Ave'),
    'city' => env('COMPANY_CITY', 'New York'),
    'state' => env('COMPANY_STATE', 'NY'),
    'zip' => env('COMPANY_ZIP', '10022'),
    'phone' => env('COMPANY_PHONE', '(555) 123-4567'),
    'email' => env('COMPANY_EMAIL', 'support@restaurantfranchisesupply.com'),
    'website' => env('COMPANY_WEBSITE', 'www.restaurantfranchisesupply.com'),
    'tax_id' => env('COMPANY_TAX_ID', '12-3456789'),
    
    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    |
    | These settings control how invoices are generated and displayed.
    |
    */
    
    'invoice_prefix' => env('INVOICE_PREFIX', 'INV-'),
    'invoice_footer_text' => env('INVOICE_FOOTER_TEXT', 'Thank you for your business!'),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | These settings control email notifications for orders and other events.
    |
    */

    'admin_notification_email' => env('ADMIN_EMAIL', 'maxgabrielua@gmail.com'),
    'warehouse_notification_email' => env('WAREHOUSE_EMAIL', 'warehouse@restaurantfranchisesupply.com'),
    'notifications_enabled' => env('ENABLE_EMAIL_NOTIFICATIONS', true),
    'send_customer_confirmation' => env('SEND_CUSTOMER_CONFIRMATION', true),

];