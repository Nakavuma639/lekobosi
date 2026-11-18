<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Booking Rules
    |--------------------------------------------------------------------------
    |
    | Business rules for event bookings. A date is "full" once the number of
    | approved bookings on it reaches max_events_per_day. Pending requests
    | never block a date. These are the owner's open questions (spec §13),
    | so each one is configurable without a code change.
    |
    */

    'bookings' => [
        'max_events_per_day' => (int) env('MAX_EVENTS_PER_DAY', 1),
        'min_lead_days' => (int) env('MIN_LEAD_DAYS', 1),
        'max_lead_years' => 2,
        'max_guests' => (int) env('MAX_GUESTS', 5000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Administrator
    |--------------------------------------------------------------------------
    |
    | Where admin notifications go, and the credentials read once by the
    | lekobosi:create-admin command. Never commit real seed credentials.
    |
    */

    'admin' => [
        'notify_email' => env('ADMIN_NOTIFY_EMAIL'),
        'whatsapp_number' => env('ADMIN_WHATSAPP_NUMBER'),
        'seed' => [
            'name' => env('ADMIN_SEED_NAME'),
            'email' => env('ADMIN_SEED_EMAIL'),
            'password' => env('ADMIN_SEED_PASSWORD'),
        ],
    ],

];
