<?php

/**
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// Homepage
$router->get('/', 'UserController@home');

// Users
$router->resource('users', 'UserController', ['except' => ['index', 'create', 'delete']]);

// Authorization flow for the Auth Code OAuth grant.
$router->get('authorize', 'AuthController@authorize');
$router->get('callback', 'AuthController@callback');

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
$router->get('password/reset', 'ForgotPasswordController@showLinkRequestForm');
$router->post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
$router->get('password/reset/{type}/{token}', ['as' => 'password.reset', 'uses' => 'ResetPasswordController@showResetForm']);
$router->post('password/reset/{type}', 'ResetPasswordController@reset');
