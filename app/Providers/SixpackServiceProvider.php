<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use SeatGeek\Sixpack\Session\Base as Sixpack;

class SixpackServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Sixpack::class, function ($app) {
            return new Sixpack([
                'baseUrl' => config('services.sixpack.url'),
                'cookiePrefix' => config('services.sixpack.prefix'),
                'timeout' => config('services.sixpack.timeout', null),
            ]);
        });
    }
}
