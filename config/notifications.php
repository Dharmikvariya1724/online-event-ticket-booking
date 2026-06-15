<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Provider
    |--------------------------------------------------------------------------
    | 'twilio' | 'vonage' | 'null' (disabled)
    */
    'sms_provider' => env('SMS_PROVIDER', 'twilio'),

    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from'  => env('TWILIO_FROM'),
    ],

    'vonage' => [
        'key'    => env('VONAGE_KEY'),
        'secret' => env('VONAGE_SECRET'),
        'from'   => env('VONAGE_FROM', 'Ticketly'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Driver (uses Laravel default mail config)
    |--------------------------------------------------------------------------
    */
    'from_address' => env('MAIL_FROM_ADDRESS', 'hello@ticketly.com'),
    'from_name'    => env('MAIL_FROM_NAME', 'Ticketly'),

    /*
    |--------------------------------------------------------------------------
    | Retry & Queue Settings
    |--------------------------------------------------------------------------
    */
    // Fall back to the app's default DB queue so queue:work/queue:listen picks notification jobs.
    'queue'        => env('NOTIFICATION_QUEUE', env('DB_QUEUE', 'default')),
    'max_tries'    => 3,
    'retry_after'  => 90,   // seconds before job unlocked for retry
    'timeout'      => 60,   // job timeout in seconds

    /*
    |--------------------------------------------------------------------------
    | Future Reminder Windows
    |--------------------------------------------------------------------------
    | These are used by the reminder scheduler (scaffold only – ready to activate)
    */
    'reminder_windows' => [
        ['hours_before' => 48, 'label' => '48h'],
        ['hours_before' => 24, 'label' => '24h'],
        ['hours_before' => 0,  'label' => 'event_day', 'at_hour' => 8],
    ],
];
