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
$router->get('authorize', 'AuthController@getAuthorize');
$router->get('callback', 'AuthController@getCallback');

// Login & Logout
$router->get('login', 'AuthController@getLogin');
$router->post('login', 'AuthController@postLogin');
$router->get('logout', 'AuthController@getLogout');

// Two-Factor Authentication
$router->get('totp', 'TotpController@prompt');
$router->post('totp', 'TotpController@verify');
$router->get('totp/configure', 'TotpController@configure');
$router->post('totp/configure', 'TotpController@store');

// Facebook Continue
$router->get('facebook/continue', 'FacebookController@redirectToProvider');
$router->get('facebook/verify', 'FacebookController@handleProviderCallback');

// Registration
$router->get('register', 'AuthController@getRegister');
$router->post('register', 'AuthController@postRegister');

//Profile routes here
$router->get('profile/about', 'ProfileAboutController@edit');
$router->post('profile/about', 'ProfileAboutContorller@store');
$router->get('profile/subscriptions', 'ProfileSubscriptionsController@edit');
$router->post('profile/subscriptions', 'ProfileSubscriptionsContorller@store');

// Password Reset
$router->get('password/reset', 'ForgotPasswordController@showLinkRequestForm');
$router->post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
$router->get('password/reset/{type}/{token}', ['as' => 'password.reset', 'uses' => 'ResetPasswordController@showResetForm']);
$router->post('password/reset/{type}', 'ResetPasswordController@reset');
