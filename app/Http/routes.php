<?php

/**
 * Set routes for the application.
 *
 * @var \Illuminate\Routing\Router $router
 * @see \Northstar\Providers\RouteServiceProvider
 */

// Redirect to some useful documentation on the homepage.
$router->get('/', function () {
    return redirect()->to('https://github.com/DoSomething/api');
});

// https://api.dosomething.org/v1/
$router->group(['prefix' => 'v1'], function () use ($router) {
    // Sessions.
    $router->post('login', 'AuthController@login');
    $router->post('logout', 'AuthController@logout');

    // Users
    $router->resource('users', 'UserController', ['except' => ['show', 'update']]);
    $router->get('users/{term}/{id}', 'UserController@show');
    $router->put('users/{term}/{id}', 'UserController@update');
    $router->post('users/{id}/avatar', 'AvatarController@store');

    // User campaign activity
    $router->get('users/{term}/{id}/campaigns', 'CampaignController@index');

    // Campaigns
    $router->get('user/campaigns/{campaign_id}', 'CampaignController@show');
    $router->post('user/campaigns/{campaign_id}/signup', 'CampaignController@signup');
    $router->post('user/campaigns/{campaign_id}/reportback', 'CampaignController@reportback');
    $router->put('user/campaigns/{campaign_id}/reportback', 'CampaignController@reportback');
    $router->post('forwardSignup', 'CampaignController@forwardSignup');

    // Kudos
    $router->post('kudos', 'KudosController@store');
    $router->delete('kudos', 'KudosController@delete');

    // Signup Groups
    $router->resource('signup-group', 'SignupGroupController');
    $router->get('signup-group/{id}', 'SignupGroupController@show');

    // Api Keys
    $router->resource('keys', 'KeyController');
});
