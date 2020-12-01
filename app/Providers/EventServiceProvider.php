<?php

namespace Northstar\Providers;

use Event;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Northstar\Events\PasswordUpdated;
use Northstar\Events\Throttled;
use Northstar\Listeners\ReportFailedAuthenticationAttempt;
use Northstar\Listeners\ReportPasswordUpdated;
use Northstar\Listeners\ReportSuccessfulAuthentication;
use Northstar\Listeners\ReportThrottledRequest;

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
