<?php

return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'resend' => ['key' => env('RESEND_KEY')],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/auth/google/callback'),
    ],
    'mapbox' => ['key' => env('MAPBOX_API_KEY')],
    'google_maps' => ['key' => env('GOOGLE_MAPS_API_KEY')],
    'whatsapp' => [
        'webhook_url' => env('WHATSAPP_WEBHOOK_URL'),
        'token' => env('WHATSAPP_WEBHOOK_TOKEN'),
        'sender_id' => env('WHATSAPP_SENDER_ID'),
    ],
];
