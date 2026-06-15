<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],
    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'stripe' => [
        'key'            => env('STRIPE_KEY'),
        'secret'         => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],
    'scanner' => [
        'token' => env('SCANNER_API_KEY'),
    ],
    'poster_ai' => [
        'provider' => env('POSTER_AI_PROVIDER', 'groq'),
        'groq' => [
            'api_key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_POSTER_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
            'url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions'),
            'timeout' => (int) env('GROQ_API_TIMEOUT', 30),
        ],
    ],
];
