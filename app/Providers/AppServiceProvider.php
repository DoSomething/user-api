<?php

namespace Northstar\Providers;

use Northstar\Models\User;
use Illuminate\Support\Facades\Log;
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

            logger('updated user', ['id' => $user->id, 'changed' => $changed]);
        });

        User::updated(function (User $user) {
            // Send payload to Blink for Customer.io profile.
            SendUserToCustomerIo::dispatch($user);
        });

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
