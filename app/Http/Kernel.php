<?php

namespace Northstar\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Fideloper\Proxy\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Northstar\Http\Middleware\VerifyCsrfToken::class,
            \Northstar\Http\Middleware\SetLanguageFromHeader::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            \Northstar\Http\Middleware\ParseOAuthHeader::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Northstar\Http\Middleware\Authenticate::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Northstar\Http\Middleware\RedirectIfAuthenticated::class,
        'scope' => \Northstar\Http\Middleware\RequireScope::class,
        'role' => \Northstar\Http\Middleware\RequireRole::class,
        'throttle' => \Northstar\Http\Middleware\ThrottleRequests::class,
    ];
}
