<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Phone Number ID
    |--------------------------------------------------------------------------
    |
    | The Phone Number ID from your Meta Business / WhatsApp Business Platform
    | dashboard. Go to: https://developers.facebook.com/apps → WhatsApp → API Setup
    | and copy the "Phone number ID" value (not the phone number itself).
    |
    */
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Access Token
    |--------------------------------------------------------------------------
    |
    | A permanent System User token (recommended for production) or a temporary
    | token from the Meta API Setup page.
    | Create a System User at: https://business.facebook.com → Settings → System Users
    |
    */
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Recipient Phone Number
    |--------------------------------------------------------------------------
    |
    | The default recipient's WhatsApp phone number in E.164 format.
    | Example: +905551234567 (country code + number, no spaces or dashes)
    | This is used as the fallback when no routeNotificationForWhatsapp() is set.
    |
    */
    'to' => env('WHATSAPP_TO', ''),

    /*
    |--------------------------------------------------------------------------
    | Meta Graph API Base URL
    |--------------------------------------------------------------------------
    |
    | The WhatsApp Business Cloud API endpoint.
    | Usually you don't need to change this.
    |
    */
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v20.0'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Timeout (seconds)
    |--------------------------------------------------------------------------
    |
    | Connection and read timeout for HTTP requests to the Meta Graph API.
    |
    */
    'timeout' => env('WHATSAPP_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | HTTP Connect Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'connect_timeout' => env('WHATSAPP_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Retry
    |--------------------------------------------------------------------------
    |
    | How many times to retry on HTTP 429 (rate limit) errors,
    | and the delay between retries in milliseconds.
    |
    */
    'retry' => [
        'times'    => env('WHATSAPP_RETRY_TIMES', 3),
        'sleep_ms' => env('WHATSAPP_RETRY_SLEEP_MS', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Proxy (optional)
    |--------------------------------------------------------------------------
    |
    | Fill this if you need to reach the Meta API through a proxy.
    | Example: 'http://proxy.example.com:8080'
    |
    */
    'proxy' => env('WHATSAPP_PROXY', null),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Set to true to write every sent message to the Laravel log.
    | Useful during development and debugging.
    |
    */
    'logging' => env('WHATSAPP_LOGGING', false),
];
