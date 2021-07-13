<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Named Queues
    |--------------------------------------------------------------------------
    |
    | We map "named" queues to environment-specific URLs here.
    |
    */
    'names' => [
        'high' => env('SQS_DEFAULT_QUEUE'),
        'low' => env('SQS_LOW_PRIORITY_QUEUE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 90,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env(
                'AWS_SQS_PREFIX',
                'https://sqs.us-east-1.amazonaws.com/your-account-id',
            ),
            'queue' => env('AWS_SQS_QUEUE', 'your-queue-name'),
            'region' => env('AWS_SQS_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 90,
            'block_for' => null,
            'after_commit' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => 'mongodb',
        'database' => 'mongodb',
        'table' => 'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Jobs For Updating/Creating Users
    |--------------------------------------------------------------------------
    |
    | This option configures which queue is used when a job is triggered by
    | updating or creating a user.
    |
    */

    'jobs' => [
        'users' => 'high',
    ],
];
