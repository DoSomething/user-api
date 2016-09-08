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

    'drupal' => [
        'url' => env('DRUPAL_API_URL'),
        'version' => 'v1',
        'username' => env('DRUPAL_API_USERNAME'),
        'password' => env('DRUPAL_API_PASSWORD'),
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
        'url' => env('FACEBOOK_API_URL'),
        'client_secret' => env('FACEBOOK_APP_SECRET'),
        'client_id' => env('FACEBOOK_APP_ID'),
    ],

];
