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

    /*
    |--------------------------------------------------------------------------
    | Security (production)
    |--------------------------------------------------------------------------
    |
    | force_https redirects plain HTTP to HTTPS and sends an HSTS header.
    | Turn it on once the site has a certificate. trusted_proxies lists the
    | load balancer / proxy addresses (or "*") so HTTPS is detected correctly
    | behind hosting proxies such as Cloudflare.
    |
    */

    'security' => [
        'force_https' => (bool) env('FORCE_HTTPS', false),
        'hsts_max_age' => (int) env('HSTS_MAX_AGE', 31536000),
        'trusted_proxies' => env('TRUSTED_PROXIES'),
    ],

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
