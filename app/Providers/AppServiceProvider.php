<?php

namespace Northstar\Providers;

use Northstar\Models\User;
use Northstar\Auth\CustomGate;
use Illuminate\Support\Facades\Log;
use Northstar\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Northstar\Database\MongoFailedJobProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

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

        // Attach the user & request ID to context for all log messages.
        Log::getMonolog()->pushProcessor(function ($record) {
            $record['extra']['user_id'] = auth()->id();
            $record['extra']['client_id'] = client_id();
            $record['extra']['request_id'] = request()->header('X-Request-Id');

            return $record;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register custom Gate with anonymous authorization support:
        $this->app->singleton(GateContract::class, function ($app) {
            return new CustomGate($app, function () use ($app) {
                return call_user_func($app['auth']->userResolver());
            });
        });

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
