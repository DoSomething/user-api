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
            \Northstar\Http\Middleware\localization::class,
        ],
        'api' => [
            \Northstar\Http\Middleware\ParseOAuthHeader::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Northstar\Http\Middleware\Authenticate::class,
        'guest' => \Northstar\Http\Middleware\RedirectIfAuthenticated::class,
        'scope' => \Northstar\Http\Middleware\RequireScope::class,
        'role' => \Northstar\Http\Middleware\RequireRole::class,
        'session_vars' => \Northstar\Http\Middleware\SessionVariablesToJavaScript::class,
    ];
}
