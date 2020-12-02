<?php

/**
 * Here is where you can register web routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "web" middleware group. Now create something great!
 *
 * @var \Illuminate\Routing\Router $router
 * @see \App\Providers\RouteServiceProvider
 */

// Homepage
Route::get('/', 'UserController@home');

// Users
Route::resource('users', 'UserController', [
    'except' => ['index', 'create', 'delete'],
]);

// Authorization flow for the Auth Code OAuth grant.
Route::get('authorize', 'AuthController@getAuthorize');
Route::get('callback', 'AuthController@getCallback');

// Login & Logout
Route::get('login', 'AuthController@getLogin');
Route::post('login', 'AuthController@postLogin');
Route::get('logout', 'AuthController@getLogout');

// Two-Factor Authentication
Route::get('totp', 'TotpController@prompt');
Route::post('totp', 'TotpController@verify');
Route::get('totp/configure', 'TotpController@configure');
Route::post('totp/configure', 'TotpController@store');

// Facebook Continue
Route::get('facebook/continue', 'FacebookController@redirectToProvider');
Route::get('facebook/verify', 'FacebookController@handleProviderCallback');

// Google Continue
Route::get('google/continue', 'GoogleController@redirectToProvider');
Route::get('google/verify', 'GoogleController@handleProviderCallback');

// Registration
Route::get('register', 'AuthController@getRegister');
Route::post('register', 'AuthController@postRegister');

// Profile
Route::get('profile/about', 'ProfileAboutController@edit');
Route::patch('profile/about', 'ProfileAboutController@update');
Route::get('profile/subscriptions', 'ProfileSubscriptionsController@edit');
Route::patch('profile/subscriptions', 'ProfileSubscriptionsController@update');

// Change Password
Route::patch('users/{id}/password', 'PasswordController@update')->name(
    'passwords.update',
);

// Password Reset
Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm');
Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail');
Route::get('password/reset/{type}/{token}', [
    'as' => 'password.reset',
    'uses' => 'ResetPasswordController@showResetForm',
]);
Route::post('password/reset/{type}', 'ResetPasswordController@reset');
