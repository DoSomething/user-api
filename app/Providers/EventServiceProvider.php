<?php

namespace App\Providers;

use App\Events\PasswordUpdated;
use App\Events\Throttled;
use App\Listeners\ReportFailedAuthenticationAttempt;
use App\Listeners\ReportPasswordUpdated;
use App\Listeners\ReportSuccessfulAuthentication;
use App\Listeners\ReportThrottledRequest;
use Event;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Login::class => [ReportSuccessfulAuthentication::class],
        Failed::class => [ReportFailedAuthenticationAttempt::class],
        Throttled::class => [ReportThrottledRequest::class],
        PasswordUpdated::class => [ReportPasswordUpdated::class],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
