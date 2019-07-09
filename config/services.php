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

    'parse' => [
        'parse_app_id' => env('PARSE_APP_ID'),
        'parse_api_key' => env('PARSE_API_KEY'),
        'parse_master_key' => env('PARSE_MASTER_KEY'),
    ],

    'stathat' => [
        'ez_key' => env('STATHAT_EZ_KEY'),
        'prefix' => env('STATHAT_APP_NAME', 'northstar').' - ',
        'debug' => env('APP_DEBUG'),
    ],

    'facebook' => [
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'client_id' => env('FACEBOOK_APP_ID'),
    ],

    'blink' => [
        'url' => env('BLINK_URL'),
        'user' => env('BLINK_USERNAME'),
        'password' => env('BLINK_PASSWORD'),
    ],

    'customerio' => [
        'url' => env('CUSTOMER_IO_URL'),
        'username' => env('CUSTOMER_IO_USERNAME'),
        'password' => env('CUSTOMER_IO_PASSWORD'),
    ],

    'puck' => [
        'url' => env('PUCK_URL'),
    ],

    'sixpack' => [
        'enabled' => env('SIXPACK_ENABLED'),
        'url' => env('SIXPACK_BASE_URL'),
        'prefix' => env('SIXPACK_COOKIE_PREFIX'),
        'timeout' => env('SIXPACK_TIMEOUT'),
    ],

    'fastly' => [
        'url' => 'https://api.fastly.com/',
        'key' => env('FASTLY_API_TOKEN'),
        'service_id' => env('FASTLY_SERVICE_ID'),
    ],
];
