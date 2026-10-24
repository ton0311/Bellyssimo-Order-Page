<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
        'templates' => [
            'order_confirmation' => env('RESEND_ORDER_CONFIRMATION_TEMPLATE') ?: 'bellyssimo-order-confirmation',
            'order_confirmed' => env('RESEND_ORDER_CONFIRMED_TEMPLATE') ?: 'bellyssimo-order-confirmed',
            'order_declined' => env('RESEND_ORDER_DECLINED_TEMPLATE') ?: 'bellyssimo-order-declined',
            'order_gcash_payment' => env('RESEND_GCASH_PAYMENT_TEMPLATE') ?: 'bellyssimo-gcash-payment',
        ],
        'gcash' => [
            'qr_url' => env('GCASH_QR_URL')
                ?: rtrim((string) env('APP_URL', 'http://localhost'), '/').'/images/gcash-payment-qr.jpeg',
        ],
        'order_notifications' => [
            'emails' => array_values(array_filter(array_map(
                'trim',
                explode(',', (string) env('ORDER_NOTIFICATION_EMAILS', '')),
            ))),
        ],
        'webhook' => [
            'secret' => env('RESEND_WEBHOOK_SECRET'),
            'tolerance' => (int) env('RESEND_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
