<?php

/**
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "api" middleware group. Now create something great!
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// https://profile.dosomething.org/v2/
$router->group(['prefix' => 'v2', 'as' => 'v2.'], function () {
    // Authentication
    $this->post('auth/token', 'OAuthController@createToken');
    $this->delete('auth/token', 'OAuthController@invalidateToken');
    $this->get('auth/info', 'OAuthController@info'); // Deprecated.
    $this->get('userinfo', 'UserInfoController@show');

    // Users
    $this->resource('users', 'UserController');
    $this->post('users/{user}/deletion', 'DeletionRequestController@store');
    $this->delete('users/{user}/deletion', 'DeletionRequestController@destroy');

    // User (by email or mobile number)
    $this->get('mobile/{mobile}', 'MobileController@show');
    $this->get('email/{email}', 'EmailController@show');

    // Subscriptions
    $this->post('subscriptions', 'SubscriptionController@create');

    // Profile
    // ...

    // OAuth Clients
    $this->resource('clients', 'ClientController');

    // Password Reset
    $this->resource('resets', 'ResetController', ['only' => 'store']);

    // Public Key
    $this->get('keys', 'KeyController@index');

    // Scopes
    $this->get('scopes', 'ScopeController@index');
});

// https://profile.dosomething.org/v1/
$router->group(['prefix' => 'v1', 'as' => 'v1.'], function () {
    // Users
    $this->resource('users', 'Legacy\UserController', ['except' => ['show', 'update']]);
    $this->get('users/{term}/{id}', 'Legacy\UserController@show');
    $this->put('users/{term}/{id}', 'Legacy\UserController@update');
    $this->post('users/{id}/merge', 'Legacy\MergeController@store');

    // Profile (the currently authenticated user)
    $this->get('profile', 'Legacy\ProfileController@show');
    $this->post('profile', 'Legacy\ProfileController@update');
});

// Discovery
$router->group(['prefix' => '.well-known'], function () {
    $this->get('openid-configuration', 'DiscoveryController@index');
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});
