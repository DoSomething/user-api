<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Test Import Form
    |--------------------------------------------------------------------------
    |
    | Defines wether to display the test import form within the admin UI.
    |
    */
    'import_test_form_enabled' => env('IMPORT_TEST_FORM_ENABLED', 'false'),

    /*
    |--------------------------------------------------------------------------
    | Email Subscription Import
    |--------------------------------------------------------------------------
    |
    | Configuration for an email subscription import.
    |
    */
    'email_subscription' => [
        'topics' => [
            'community' => [
                'reset' => [
                    'enabled' => env(
                        'COMMUNITY_SUBSCRIPTION_RESET_ENABLED',
                        'true',
                    ),
                    'type' => env(
                        'COMMUNITY_SUBSCRIPTION_RESET_TYPE',
                        'wyd-activate-account',
                    ),
                ],
            ],

            'lifestyle' => [
                'reset' => [
                    'enabled' => env(
                        'LIFESTYLE_SUBSCRIPTION_RESET_ENABLED',
                        'true',
                    ),
                    'type' => env(
                        'LIFESTYLE_SUBSCRIPTION_RESET_TYPE',
                        'boost-activate-account',
                    ),
                ],
            ],

            'news' => [
                'reset' => [
                    'enabled' => env('NEWS_SUBSCRIPTION_RESET_ENABLED', 'true'),
                    'type' => env(
                        'NEWS_SUBSCRIPTION_RESET_TYPE',
                        'breakdown-activate-account',
                    ),
                ],
            ],

            'scholarships' => [
                'reset' => [
                    'enabled' => env(
                        'SCHOLARSHIPS_SUBSCRIPTION_RESET_ENABLED',
                        'true',
                    ),
                    'type' => env(
                        'SCHOLARSHIPS_SUBSCRIPTION_RESET_TYPE',
                        'pays-to-do-good-activate-account',
                    ),
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Voter Registration Import
    |--------------------------------------------------------------------------
    |
    | Configuration for a Rock The Vote voter registration import.
    |
    */
    'rock_the_vote' => [
        // Whether to automatically retry a report with failed status.
        'retry_failed_reports' => env(
            'ROCK_THE_VOTE_RETRY_FAILED_REPORTS_ENABLED',
            false,
        ),

        // Constants to use when creating a post.
        'post' => [
            'action_id' => env('ROCK_THE_VOTE_POST_ACTION_ID', 954),
            'type' => env('ROCK_THE_VOTE_POST_TYPE', 'voter-reg'),
            'source' => env('ROCK_THE_VOTE_POST_SOURCE', 'rock-the-vote'),

            // These correspond to column names in a Rock The Vote report.
            'details' => [
                'Tracking Source',
                'Started registration',
                'Finish with State',
                'Status',
                'Pre-Registered',
                'Home zip code',
            ],
        ],

        // Configuration for sending the Activate Account password reset email to new users.
        'reset' => [
            'enabled' => env('ROCK_THE_VOTE_RESET_ENABLED', 'true'),
            'type' => env(
                'ROCK_THE_VOTE_RESET_TYPE',
                'rock-the-vote-activate-account',
            ),
        ],

        /*
         * This list includes status values we import from Rock The Vote, values we once
         * translated from Rock the Vote (e.g. uncertain, ineligible), or values that a user
         * may set via the web (e.g. unregistered).
         */
        'status_hierarchy' => [
            'uncertain',
            'ineligible',
            'under-18',
            'rejected',
            'unregistered',
            'step-1',
            'step-2',
            'step-3',
            'step-4',
            'confirmed',
            'register-OVR',
            'register-form',
            'registration_complete',
        ],

        // Constants to use when creating a new user.
        'user' => [
            'email_subscription_topics' => env(
                'ROCK_THE_VOTE_EMAIL_SUBSCRIPTION_TOPICS',
                'community',
            ),
            'sms_subscription_topics' => env(
                'ROCK_THE_VOTE_SMS_SUBSCRIPTION_TOPICS',
                'voting',
            ),
            'source_detail' => env(
                'ROCK_THE_VOTE_USER_SOURCE_DETAIL',
                'rock-the-vote',
            ),
        ],
    ],
];
