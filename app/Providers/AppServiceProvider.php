<?php

namespace Northstar\Providers;

use Illuminate\Support\ServiceProvider;
use Northstar\Models\User;
use Northstar\Observers\UserObserver;

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
        // ...
    }
}
