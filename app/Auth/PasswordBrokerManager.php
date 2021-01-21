<?php

namespace App\Auth;

use Illuminate\Auth\Passwords\PasswordBrokerManager as BasePasswordBrokerManager;
use Jenssegers\Mongodb\Auth\DatabaseTokenRepository;

class PasswordBrokerManager extends BasePasswordBrokerManager
{
    /**
     * {@inheritdoc}
     */
    protected function createTokenRepository(array $config)
    {
        return new DatabaseTokenRepository(
            $this->app['db']->connection('mongodb'), // HACK: We swap to our non-default connection here.
            $this->app['hash'],
            $config['table'],
            $this->app['config']['app.key'],
            $config['expire'],
        );
    }
}
