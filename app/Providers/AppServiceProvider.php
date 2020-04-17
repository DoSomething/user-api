<?php

namespace Northstar\Providers;

use Northstar\Models\User;
use Northstar\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Northstar\Database\MongoFailedJobProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Attach model observer(s):
        User::observe(UserObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Configure Mongo 'failed_jobs' collection.
        $this->app->extend('queue.failer', function ($instance, $app) {
            return new MongoFailedJobProvider(
                $app['db'],
                config('queue.failed.database'),
                config('queue.failed.table')
            );
        });
    }
}
