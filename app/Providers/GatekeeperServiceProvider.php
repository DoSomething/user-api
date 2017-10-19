<?php

namespace Northstar\Providers;

use League\OAuth2\Server\CryptKey;
use Northstar\Auth\NorthstarJwtGuard;
use Illuminate\Support\ServiceProvider;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\ResourceServer;

class GatekeeperServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // ...
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /** @var \Illuminate\Auth\AuthManager $auth */
        $auth = $this->app['auth'];
        $auth->extend('jwt', function ($app, $name, array $config) use ($auth) {
            $key = new CryptKey($config['key']);
            $provider = $auth->createUserProvider($config['provider']);

            return new NorthstarJwtGuard($name, $key, $provider, $app['request']);
        });
    }
}
