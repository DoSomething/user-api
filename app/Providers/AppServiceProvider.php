<?php

namespace Northstar\Providers;

use Illuminate\Auth\NorthstarJwtGuard;
use Northstar\Models\User;
use DoSomething\Gateway\Blink;
use Illuminate\Support\ServiceProvider;

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
            if (config('features.blink')) {
                app(Blink::class)->userCreate($user->toBlinkPayload());
            }

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
            if (config('features.blink') && config('features.blink-updates')) {
                app(Blink::class)->userCreate($user->toBlinkPayload());
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
