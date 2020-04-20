<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Driver
    |--------------------------------------------------------------------------
    |
    | The Laravel queue API supports a variety of back-ends via an unified
    | API, giving you convenient access to each back-end using the same
    | syntax for each one. Here you may set the default queue driver.
    |
    | Supported: "null", "sync", "database", "beanstalkd",
    |            "sqs", "iron", "redis"
    |
    */

    'default' => env('QUEUE_DRIVER', 'sync'),

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
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'expire' => 60,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'ttr' => 60,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key'    => env('AWS_ACCESS_KEY'),
            'secret' => env('AWS_SECRET_KEY'),
            'queue'  => env('SQS_DEFAULT_QUEUE'),
            'region' => 'us-east-1',
        ],

        'iron' => [
            'driver' => 'iron',
            'host' => 'mq-aws-us-east-1.iron.io',
            'token' => 'your-token',
            'project' => 'your-project-id',
            'queue' => 'your-queue-name',
            'encrypt' => true,
        ],

        'redis' => [
            'driver' => 'redis',
            'queue' => 'default',
            'expire' => 60,
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
