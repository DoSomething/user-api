<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'analytics' => [
        'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
        'snowplow_url' => env('SNOWPLOW_URL'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_KEY'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],

    'phoenix' => [
        'url' => env('PHOENIX_URL'),
    ],

    'facebook' => [
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'client_id' => env('FACEBOOK_APP_ID'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'graphql' => [
        'url' => env('GRAPHQL_URL'),
    ],

    'rogue' => [
        'url' => env('ROGUE_URL'),
    ],

    'gambit' => [
        'url' => env('GAMBIT_URL'),
        'user' => env('GAMBIT_USERNAME'),
        'password' => env('GAMBIT_PASSWORD'),
    ],

    'customerio' => [
        'url' => 'https://track.customer.io/api/v1/',
        'username' => env('CUSTOMER_IO_USERNAME'),
        'password' => env('CUSTOMER_IO_PASSWORD'),
    ],

    'sixpack' => [
        'enabled' => env('SIXPACK_ENABLED'),
        'url' => env('SIXPACK_BASE_URL'),
        'prefix' => env('SIXPACK_COOKIE_PREFIX'),
        'timeout' => env('SIXPACK_TIMEOUT'),
    ],

    'fastly' => [
        'url' => 'https://api.fastly.com/',
        'api_key' => env('FASTLY_API_KEY'),
        'redirects_table' => env('FASTLY_TABLE_REDIRECTS'),
        'frontend_url' => env('FASTLY_FRONTEND_SERVICE_URL'),
        'services' => [
            'frontend' => env('FASTLY_FRONTEND_SERVICE_ID'),
            'backend' => env('FASTLY_BACKEND_SERVICE_ID'),
        ],
    ],
];
