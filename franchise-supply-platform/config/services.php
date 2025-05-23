<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'quickbooks' => [
        'client_id' => env('QB_CLIENT_ID'),
        'client_secret' => env('QB_CLIENT_SECRET'),
        'redirect_uri' => env('QB_REDIRECT_URI'),
        'scope' => env('QB_SCOPE', 'com.intuit.quickbooks.accounting'),
        'base_url' => env('QB_API_BASE_URL', 'https://quickbooks.api.intuit.com/v3/company/'),
        'auth_endpoint' => env('QB_AUTH_ENDPOINT', 'https://appcenter.intuit.com/connect/oauth2'),
        'token_endpoint' => env('QB_TOKEN_ENDPOINT', 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer'),
        'integration_enabled' => env('QB_INTEGRATION_ENABLED', false),
    ],

    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
        'templates' => [
            'order_confirmation' => env('SENDGRID_ORDER_CONFIRMATION_TEMPLATE'),
            'order_notification_admin' => env('SENDGRID_ORDER_NOTIFICATION_ADMIN_TEMPLATE'),
            'order_notification_warehouse' => env('SENDGRID_ORDER_NOTIFICATION_WAREHOUSE_TEMPLATE'),
        ],
        'tracking' => [
            'enabled' => env('SENDGRID_TRACKING_ENABLED', true),
            'click_tracking' => env('SENDGRID_CLICK_TRACKING', true),
            'open_tracking' => env('SENDGRID_OPEN_TRACKING', true),
        ],
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'), // Format: +1234567890
        'enabled' => env('TWILIO_ENABLED', false),
        'templates' => [
            'order_confirmation' => env('TWILIO_ORDER_CONFIRMATION_TEMPLATE', 'Your order #{{1}} has been confirmed and will be processed soon. Total: {{2}}'),
            'order_notification' => env('TWILIO_ORDER_NOTIFICATION_TEMPLATE', 'New order #{{1}} requires your attention. From: {{2}}, Amount: {{3}}'),
        ],
    ],

    'firebase' => [
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'credentials_path' => env('FIREBASE_CREDENTIALS_PATH'),
    ],
];
