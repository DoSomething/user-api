<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If ['*'] is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    // You can enable CORS for 1 or multiple paths. Example: ['api/*']
    'paths' => ['api/*', 'images/*', 'v2/users/*', 'v2/subscriptions'],

    // Matches the request method. `['*']` allows all methods.
    'allowed_methods' => ['*'],

    // Matches the request origin. `['*']` allows all origins. Wildcards can be used, eg `*.mydomain.com`
    'allowed_origins' => ['https://*.dosomething.org', 'http://phoenix.test'],

    // Patterns that can be used with `preg_match` to match the origin.
    'allowed_origins_patterns' => [],

    // Sets the Access-Control-Allow-Headers response header. `['*']` allows all headers.
    'allowed_headers' => ['*'],

    // Sets the Access-Control-Expose-Headers response header with these headers.
    'exposed_headers' => [],

    // Sets the Access-Control-Max-Age response header when > 0.
    'max_age' => 0,

    // Sets the Access-Control-Allow-Credentials header.
    'supports_credentials' => true,
];
