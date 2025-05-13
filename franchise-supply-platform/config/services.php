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

];
