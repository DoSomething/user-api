<?php

/**
 * Set routes for the application.
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// Web Experience for https://northstar.dosomething.org/

$router->get('test', function() {
    $jwt = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjM3ZWM3ZDFhYmEzOTMzYmZiZDlkM2JiZmY1OWEyZDE3YzJhYTQwNDI2Y2U3ZWM4MjcxMTNkNjg4YmI5MDU1Y2U5NzRiZjVkZTBlZDE2NWNiIn0.eyJpc3MiOiJodHRwOlwvXC9ub3J0aHN0YXIuZHMuZGV2IiwiYXVkIjoidHJ1c3RlZC10ZXN0LWNsaWVudCIsImp0aSI6IjM3ZWM3ZDFhYmEzOTMzYmZiZDlkM2JiZmY1OWEyZDE3YzJhYTQwNDI2Y2U3ZWM4MjcxMTNkNjg4YmI5MDU1Y2U5NzRiZjVkZTBlZDE2NWNiIiwiaWF0IjoxNDk3MjE3MTIzLCJuYmYiOjE0OTcyMTcxMjMsImV4cCI6MTQ5NzIyMDcyMywic3ViIjoiNTkxYjFmN2E5YTg5MjA0YTY1MzZhNTI1Iiwicm9sZSI6InVzZXIiLCJzY29wZXMiOlsidXNlciIsInJvbGU6c3RhZmYiLCJyb2xlOmFkbWluIl19.oLtckNSGPW8o-NmZHhH_yTB51ptszfVCOqyZd7dSjb_4XC7_daFZ7aTW2sEwDtt7feQG3Ur68gky-LdxTCm1BcQt24brxBZh6820_mFLzBoq1G3IVqMAG22vHVd5xcf7XX_YILMVDoRZGZDtPfnp2OJJcHzkEXAWRajn6AuKmiJbJqtqa8dq_uaRtt6BCqIcKHaaqYsTIkGjq77Oas7IjSKV9-mRrs2oiY1KtfT5UMn5xwbyX6DZiw64RPLpZef7skU8Ni64rg3nIy61IAkF8o4f0GJQ4WSjszwxe3EGQHZkTULeeFL4BESDaS-QzuNFl17ym8ppCnTQdQNiwcPq-sTBOVDvvM-pX7YSk56Jgk2ljtIA0338fZazNHKxIJnZMkWMOhp4_IlMUnJna0YX6-lOUTHb7gWuMQ9lZpgIbVOKlDS_UTgoLWr_2mbYaUUo-vILz61_L8MNPJHkOcFh2GQcP38bGaSr3BxXz5kj8u5xPKnCev_sjC6lkfE3VZTbjIpC5PgUmc_gtiU1wuE1YCRBOJ_FcJSB0HJBQbibisEEHCzugEg7Ah2Z2mgDSwAVfDUaoDfA7i31VOffzSlPtlKq_GVzj9av_huLiEfpKg_S_8nLGYengEn1DRioxR7s5XXUgAqOa2tujXouHP4jrmH9RxtJju5mAzSkwmB_Pxs';

    return response('ok')->withCookie(cookie('northstar_token', $jwt, 60, null, 'ds.dev', false, false));
});

$router->group(['namespace' => 'Web', 'guard' => 'web', 'middleware' => ['web']], function () use ($router) {
    $router->get('/', 'UserController@home');

    // Users
    $router->resource('users', 'UserController', ['except' => ['index', 'create', 'delete']]);

    // Authorization flow for the Auth Code OAuth grant.
    $router->get('authorize', 'AuthController@authorize');

    // Login & Logout
    $router->get('login', 'AuthController@getLogin');
    $router->post('login', 'AuthController@postLogin');
    $router->get('logout', 'AuthController@getLogout');

    // Facebook Continue
    $router->get('facebook/continue', 'FacebookController@redirectToProvider');
    $router->get('facebook/verify', 'FacebookController@handleProviderCallback');

    // Unsubscribes
    $router->get('unsubscribe', 'UnsubscribeController@getSubscriptions');
    $router->post('unsubscribe', 'UnsubscribeController@postSubscriptions');

    // Registration
    $router->get('register', 'AuthController@getRegister');
    $router->post('register', 'AuthController@postRegister');

    // Password Reset
    $this->get('password/reset', 'ForgotPasswordController@showLinkRequestForm');
    $this->post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
    $this->get('password/reset/{token}', 'ResetPasswordController@showResetForm');
    $this->post('password/reset', 'ResetPasswordController@reset');
});

// API experience for https://nortstar.dosomething.org/v2/
$router->group(['prefix' => 'v2', 'as' => 'v2.', 'middleware' => ['api']], function () use ($router) {
    // Authentication
    $router->post('auth/token', 'OAuthController@createToken');
    $router->get('auth/info', 'OAuthController@info');
    $router->delete('auth/token', 'OAuthController@invalidateToken');

    // Users
    // ...

    // Profile
    // ...

    // OAuth Clients
    $router->resource('clients', 'ClientController');

    // Password Reset
    $router->resource('resets', 'ResetController', ['only' => 'store']);

    // Public Key
    $router->get('keys', 'KeyController@index');
    $router->get('key', 'KeyController@show'); // Deprecated.

    // Scopes
    $router->get('scopes', 'ScopeController@index');
});

// API experience for https://northstar.dosomething.org/v1/
$router->group(['prefix' => 'v1', 'as' => 'v1.', 'middleware' => ['api']], function () use ($router) {
    // Authentication
    $router->post('auth/token', 'Legacy\AuthController@createToken');
    $router->post('auth/invalidate', 'Legacy\AuthController@invalidateToken');
    $router->post('auth/verify', 'Legacy\AuthController@verify');
    $router->post('auth/register', 'Legacy\AuthController@register');
    $router->post('auth/phoenix', 'Legacy\AuthController@phoenix');

    // Users
    $router->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $router->get('users/{term}/{id}', 'UserController@show');
    $router->put('users/{term}/{id}', 'UserController@update');
    $router->post('users/{id}/avatar', 'AvatarController@store');
    $router->post('users/{id}/merge', 'MergeController@store');

    // Profile (the currently authenticated user)
    $router->get('profile', 'ProfileController@show');
    $router->post('profile', 'ProfileController@update');
    $router->get('profile/signups', 'Legacy\SignupController@profile');
    $router->get('profile/reportbacks', 'Legacy\ReportbackController@profile');

    // Signups & Reportbacks (Phoenix)
    $router->resource('signups', 'Legacy\SignupController', ['only' => ['index', 'show', 'store']]);
    $router->resource('reportbacks', 'Legacy\ReportbackController', ['only' => ['index', 'show', 'store']]);
});

// Discovery
$router->group(['prefix' => '.well-known'], function () use ($router) {
    $router->get('openid-configuration', 'DiscoveryController@index');
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});
