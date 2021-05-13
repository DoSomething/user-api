<?php

use Illuminate\Support\Facades\Route;

/*
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * contains the "api" middleware group. Now create something great!
 *
 * @var \Illuminate\Routing\Router $router
 * @see \App\Providers\RouteServiceProvider
 */

Route::group(
    // TODO: Do we want to use 'api/' prefix for v1 & v2 routes too?
    ['prefix' => 'api/v3', 'middleware' => ['guard:api']],
    function () {
        // Actions
        Route::get('actions', 'ActionsController@index');
        Route::get('actions/{action}', 'ActionsController@show');

        // Action Stats
        Route::get('action-stats', 'ActionStatsController@index');

        // Campaigns
        Route::get('campaigns', 'CampaignsController@index');
        Route::get('campaigns/{campaign}', 'CampaignsController@show');
        Route::patch('campaigns/{campaign}', 'CampaignsController@update');

        // Clubs
        Route::get('clubs', 'ClubsController@index');
        Route::get('clubs/{club}', 'ClubsController@show');

        // Groups
        Route::get('groups', 'GroupsController@index');
        Route::get('groups/{group}', 'GroupsController@show');

        // Group Types
        Route::get('group-types', 'GroupTypesController@index');
        Route::get('group-types/{groupType}', 'GroupTypesController@show');

        // Posts
        Route::post('posts', 'PostsController@store');
        Route::get('posts', 'PostsController@index');
        Route::get('posts/{post}', 'PostsController@show');
        Route::patch('posts/{post}', 'PostsController@update');
        Route::delete('posts/{post}', 'PostsController@destroy');

        // Posts: Reactions
        Route::post('posts/{post}/reactions', 'ReactionController@store');
        Route::get('posts/{post}/reactions', 'ReactionController@index');

        // Posts: Tags
        Route::post('posts/{post}/tags', 'TagsController@store');

        // Posts: Reviews
        Route::post('posts/{post}/reviews', 'ReviewsController@store');

        // Posts: Rotate
        Route::post('posts/{post}/rotate', 'RotationController@update');

        // Signups
        Route::post('signups', 'SignupsController@store');
        Route::get('signups', 'SignupsController@index');
        Route::get('signups/{signup}', 'SignupsController@show');
        Route::patch('signups/{signup}', 'SignupsController@update');
        Route::delete('signups/{signup}', 'SignupsController@destroy');

        // Questionnaires
        Route::post('questionnaires', 'QuestionnaireController@store');
    },
);

// https://identity.dosomething.org/v2/
Route::group(['prefix' => 'v2', 'as' => 'v2.'], function () {
    // Authentication
    Route::post('auth/token', 'OAuthController@createToken');
    Route::delete('auth/token', 'OAuthController@invalidateToken');
    Route::get('auth/info', 'OAuthController@info'); // Deprecated.
    Route::get('userinfo', 'UserInfoController@show');

    // Users
    Route::resource('users', 'UserController');
    Route::post('users/{user}/deletion', 'DeletionRequestController@store');
    Route::delete('users/{user}/deletion', 'DeletionRequestController@destroy');

    // User (by email or mobile number)
    Route::get('mobile/{mobile}', 'MobileController@show');
    Route::get('email/{email}', 'EmailController@show');

    // Subscriptions
    Route::post('subscriptions', 'SubscriptionController@create');

    // Email Subscriptions
    Route::post(
        'users/{user}/subscriptions/{topic}',
        'SubscriptionUpdateController@store',
    );
    Route::delete(
        'users/{user}/subscriptions/{topic}',
        'SubscriptionUpdateController@destroy',
    );

    // Promotions
    Route::delete('users/{id}/promotions', 'PromotionsController@destroy');
    /*
     * HACK: Gateway PHP does not correctly parse our delete request response, so expose a POST
     * route to mute promotions and inspect the response.
     * @see https://github.com/DoSomething/chompy/pull/202
     */
    Route::post('users/{id}/promotions', 'PromotionsController@destroy');

    // Cause Preferences
    Route::post('users/{user}/causes/{cause}', 'CauseUpdateController@store');
    Route::delete(
        'users/{user}/causes/{cause}',
        'CauseUpdateController@destroy',
    );

    // Profile
    // ...

    // Password Reset
    Route::resource('resets', 'ResetController', ['only' => 'store']);

    // Public Key
    Route::get('keys', 'KeyController@index');

    // Scopes
    Route::get('scopes', 'ScopeController@index');
});

// https://identity.dosomething.org/v1/
Route::group(['prefix' => 'v1', 'as' => 'v1.'], function () {
    // Users
    Route::resource('users', 'Legacy\UserController', [
        'except' => ['show', 'update'],
    ]);
    Route::get('users/{term}/{id}', 'Legacy\UserController@show');
    Route::put('users/{term}/{id}', 'Legacy\UserController@update');
    Route::post('users/{id}/merge', 'Legacy\MergeController@store');

    // Profile (the currently authenticated user)
    Route::get('profile', 'Legacy\ProfileController@show');
    Route::post('profile', 'Legacy\ProfileController@update');
});

// https://identity.dosomething.org/api/partners/
Route::group(['prefix' => 'api/partners'], function () {
    // CallPower
    Route::post('callpower/call', 'Partners\CallPowerController@store');
});

// Assets
Route::get('images/{hash}', 'Web\ImagesController@show');

// Discovery
Route::group(['prefix' => '.well-known'], function () {
    Route::get('openid-configuration', 'DiscoveryController@index');
});

// Simple health check endpoint
Route::get('/status', function () {
    return ['status' => 'good'];
});
