<?php

return [
    // Service Fee
    'service_fee_percentage' => env('TICKETLY_SERVICE_FEE_PCT', 5),

    // Portal Fee
    'portal_fee_percentage'  => env('TICKETLY_PORTAL_FEE_PCT', 10),

    // Ticket Hold
    'hold_minutes'           => env('TICKETLY_HOLD_MINUTES', 10),

    // Settlement Days
    'settlement_days'        => env('TICKETLY_SETTLEMENT_DAYS', 0),

    // Currency
    'currency'               => env('TICKETLY_CURRENCY', 'GBP'),
    'currency_symbol'        => env('TICKETLY_CURRENCY_SYMBOL', '£'),

    //Support
    'support_email'          => env('SUPPORT_EMAIL', 'support@ticketly.com'),
];
