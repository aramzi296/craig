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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    /*
    |--------------------------------------------------------------------------
    | WhatsApp (GOWA – go-whatsapp-web-multidevice)
    |--------------------------------------------------------------------------
    |
    | Configure the self-hosted GOWA REST API used to send/receive messages.
    |
    */
    'whatsapp' => [
        'api_url'   => env('WHATSAPP_API_URL', ''),
        'device_id' => env('WHATSAPP_DEVICE_ID', 'default'),
        'api_user'  => env('WHATSAPP_API_USER', 'admin'),
        'api_pass'  => env('WHATSAPP_API_PASS', ''),
    ],

];
