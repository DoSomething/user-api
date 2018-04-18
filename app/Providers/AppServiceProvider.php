<?php

namespace Northstar\Providers;

use Northstar\Models\User;
use Illuminate\Support\ServiceProvider;
use Northstar\Jobs\SendUserToCustomerIo;
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
        User::creating(function (User $user) {
            // Set source automatically if not provided.
            $user->source = $user->source ?: client_id();
        });

        User::created(function (User $user) {
            // Send payload to Blink for Customer.io profile.
            SendUserToCustomerIo::dispatch($user);

            // Send metrics to StatHat.
            app('stathat')->ezCount('user created');
            app('stathat')->ezCount('user created - '.$user->source);
        });

        User::updating(function (User $user) {
            // Write profile changes to the log, with redacted values for hidden fields.
            $changed = array_replace_keys($user->getDirty(), $user->getHidden(), '*****');
            logger('updated user', ['id' => $user->id, 'client_id' => client_id(), 'changed' => $changed]);
        });

        User::updated(function (User $user) {
            // Send payload to Blink for Customer.io profile.
            SendUserToCustomerIo::dispatch($user);
        });
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
