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
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Northstar\Http\Middleware\LogMemoryUsage::class,
        \Northstar\Http\Middleware\TrimStrings::class,
        \Northstar\Http\Middleware\TrustProxies::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Northstar\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Northstar\Http\Middleware\VerifyCsrfToken::class,
            \Northstar\Http\Middleware\SetLanguageFromHeader::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            'guard:web',
        ],
        'api' => [
            \Northstar\Http\Middleware\ParseOAuthHeader::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Barryvdh\Cors\HandleCors::class,
            'guard:api',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'guard' => \DoSomething\Gateway\Server\Middleware\SetGuard::class,
        'auth' => \Northstar\Http\Middleware\Authenticate::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Northstar\Http\Middleware\RedirectIfAuthenticated::class,
        'scope' => \Northstar\Http\Middleware\RequireScope::class,
        'role' => \Northstar\Http\Middleware\RequireRole::class,
        'throttle' => \Northstar\Http\Middleware\ThrottleRequests::class,
    ];
}
