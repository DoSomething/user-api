<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
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
        // Attach model observer(s):
        User::observe(UserObserver::class);

        // Register our custom pagination templates.
        Paginator::defaultView('pagination::default');
        Paginator::defaultSimpleView('pagination::simple-default');
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
