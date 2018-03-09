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
    $this->resource('users', 'Two\UserController');
    // Get User by Mobile #
    $this->get('mobile/{id}', 'Two\MobileController@show');

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
    $this->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $this->get('users/{term}/{id}', 'UserController@show');
    $this->put('users/{term}/{id}', 'UserController@update');
    $this->post('users/{id}/avatar', 'AvatarController@store');
    $this->post('users/{id}/merge', 'MergeController@store');

    // Profile (the currently authenticated user)
    $this->get('profile', 'ProfileController@show');
    $this->post('profile', 'ProfileController@update');
});

// Discovery
$router->group(['prefix' => '.well-known'], function () {
    $this->get('openid-configuration', 'DiscoveryController@index');
});

// Simple health check endpoint
$router->get('/status', function () {
    return ['status' => 'good'];
});
