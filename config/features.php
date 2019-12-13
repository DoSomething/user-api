<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | This file is a custom addition to Northstar for storing feature flags, so
    | features can be conditionally toggled on and off per environment.
    |
    */

    'blink' => env('DS_ENABLE_BLINK'),

    'password-grant' => env('DS_ENABLE_PASSWORD_GRANT', true),

    'rate-limiting' => env('DS_ENABLE_RATE_LIMITING'),

    'badges' => env('DS_BADGES_TEST', false),

    'optional-fields' => env('DS_OPTIONAL_FIELDS', false),

    'refer-friends-scholarship' => env('DS_REFER_FRIENDS_SCHOLARSHIP_TEST', false),

    'no-badge-campaigns' => explode(',', env('DS_CONTENTFUL_IDS_FOR_CAMPAIGNS_WITH_NO_BADGES', null)),
];
