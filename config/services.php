<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'analytics' => [
        'google_tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID'),
        'snowplow_url' => env('SNOWPLOW_URL'),
    ],

    'customerio' => [
        'app_api' => [
            'api_key' => env('CUSTOMER_IO_APP_API_KEY'),
            'url' => 'https://api.customer.io/v1/',
            'identifier_id' => env(
                'CUSTOMER_IO_APP_IDENTIFIER_ID',
                'app-api-user',
            ),
            'transactional_message_ids' => [
                'FORGOT_PASSWORD' => env(
                    'CUSTOMER_IO_FORGOT_PASSWORD_TRANSACTIONAL_MESSAGE_ID',
                    2,
                ),
                'PASSWORD_UPDATED' => env(
                    'CUSTOMER_IO_PASSWORD_UPDATED_TRANSACTIONAL_MESSAGE_ID',
                    3,
                ),
            ],
        ],
        'track_api' => [
            'url' => 'https://track.customer.io/api/v1/',
            'username' => env('CUSTOMER_IO_USERNAME'),
            'password' => env('CUSTOMER_IO_PASSWORD'),
        ],
    ],

    'facebook' => [
        'redirect' => env('FACEBOOK_REDIRECT_URL'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'client_id' => env('FACEBOOK_APP_ID'),
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

    'gambit' => [
        'url' => env('GAMBIT_URL'),
        'user' => env('GAMBIT_USERNAME'),
        'password' => env('GAMBIT_PASSWORD'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL'),
    ],

    'graphql' => [
        'url' => env('GRAPHQL_URL'),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'mandrill' => [
        'secret' => env('MANDRILL_SECRET'),
    ],

    'phoenix' => [
        'url' => env('PHOENIX_URL'),
    ],

    'rock_the_vote' => [
        'api_key' => env('ROCK_THE_VOTE_API_KEY'),
        'partner_id' => env('ROCK_THE_VOTE_PARTNER_ID'),
        'url' => env('ROCK_THE_VOTE_API_URL'),
        // Used for local development to avoid making API requests.
        'faker' => env('ROCK_THE_VOTE_API_FAKER', false),
    ],

    'sixpack' => [
        'enabled' => env('SIXPACK_ENABLED'),
        'url' => env('SIXPACK_BASE_URL'),
        'prefix' => env('SIXPACK_COOKIE_PREFIX'),
        'timeout' => env('SIXPACK_TIMEOUT'),
    ],

    'slack' => [
        'url' => env('SLACK_WEBHOOK_INTEGRATION_URL'),
    ],
];
