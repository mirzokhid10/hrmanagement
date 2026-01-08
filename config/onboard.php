<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Base Domain Configuration
    |--------------------------------------------------------------------------
    |
    | The base domain for your application. Subdomains will be created
    | based on this domain.
    |
    | Local: onboard.test
    | Production: onboard.uz
    */
    'base_domain' => env('APP_URL_BASE_DOMAIN', 'onboard.test'),

    /*
    |--------------------------------------------------------------------------
    | Tenant Identification
    |--------------------------------------------------------------------------
    |
    | How tenants are identified in your application.
    | Options: 'subdomain', 'path', 'header'
    */
    'tenant_identification' => 'subdomain',

    /*
    |--------------------------------------------------------------------------
    | Tenant Cache TTL
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) to cache tenant lookups.
    | Default: 3600 (1 hour)
    */
    'tenant_cache_ttl' => env('TENANT_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Reserved Subdomains
    |--------------------------------------------------------------------------
    |
    | Subdomains that cannot be used for tenant registration.
    */
    'reserved_subdomains' => [
        'www',
        'admin',
        'api',
        'app',
        'mail',
        'ftp',
        'blog',
        'shop',
        'support',
        'help',
        'dev',
        'staging',
        'test',
    ],

    /*
    |--------------------------------------------------------------------------
    | HH.ru Integration
    |--------------------------------------------------------------------------
    */
    'hhru' => [
        'enabled' => env('HHRU_ENABLED', false),
        'client_id' => env('HHRU_CLIENT_ID'),
        'client_secret' => env('HHRU_CLIENT_SECRET'),
        'redirect_uri' => env('HHRU_REDIRECT_URI'),
        'api_url' => env('HHRU_API_URL', 'https://api.hh.ru'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Telegram Bot
    |--------------------------------------------------------------------------
    */
    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Time Off Types
    |--------------------------------------------------------------------------
    |
    | Default time off types that will be created for new tenants.
    */
    'default_time_off_types' => [
        [
            'name' => 'Annual Leave',
            'description' => 'Paid annual leave',
            'is_paid' => true,
            'default_days_per_year' => 20,
        ],
        [
            'name' => 'Sick Leave',
            'description' => 'Paid sick leave',
            'is_paid' => true,
            'default_days_per_year' => 10,
        ],
        [
            'name' => 'Unpaid Leave',
            'description' => 'Unpaid time off',
            'is_paid' => false,
            'default_days_per_year' => 0,
        ],
    ],
];
