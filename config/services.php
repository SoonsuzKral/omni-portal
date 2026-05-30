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

    'google' => [
        'indexing_credentials' => env('GOOGLE_INDEXING_CREDENTIALS') ? json_decode(env('GOOGLE_INDEXING_CREDENTIALS'), true) : null,
        'indexing_service_account' => env('GOOGLE_INDEXING_SERVICE_ACCOUNT'),
    ],

    'bing' => [
        'indexing_api_key' => env('BING_INDEXING_API_KEY'),
    ],

    'adsense' => [
        'publisher_id' => env('ADSENSE_PUBLISHER_ID'),
        'ad_client' => env('ADSENSE_AD_CLIENT', 'ca-pub-xxxxxxxxxxxxx'),
        'header_slot' => env('ADSENSE_AD_SLOT_HEADER'),
        'sidebar_slot' => env('ADSENSE_AD_SLOT_SIDEBAR'),
        'inarticle_slot' => env('ADSENSE_AD_SLOT_INARTICLE'),
        'footer_slot' => env('ADSENSE_AD_SLOT_FOOTER'),
        'enabled' => !empty(env('ADSENSE_PUBLISHER_ID')) && !empty(env('ADSENSE_AD_CLIENT')),
    ],

    'google_analytics' => [
        'measurement_id' => env('GA4_MEASUREMENT_ID'),
        'api_secret' => env('GA4_API_SECRET'),
        'property_id' => env('GA4_PROPERTY_ID'),
    ],

];
