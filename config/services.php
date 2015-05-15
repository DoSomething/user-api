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
    ],

    'S3' => [
        'url' => env('S3_URL'),
        'key' => env('S3_KEY'),
        'secret' => env('S3_SECRET'),
    ],

];
