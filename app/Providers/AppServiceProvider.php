<?php

namespace Northstar\Providers;

use Illuminate\Support\ServiceProvider;
use Northstar\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        User::creating(function ($user) {
            // Set source automatically if not provided.
//            $user->source = $user->source ?: client_id();
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
