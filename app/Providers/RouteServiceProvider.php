<?php

namespace App\Providers;

use App\Auth\Registrar;
use App\Services\Fastly;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     * @see https://laravel.com/docs/8.x/upgrade#automatic-controller-namespace-prefixing consider
     * proceeding with above upgrade to make development easier in IDEs.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The registrar handles creating, updating, and
     * validating user accounts.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Route::model('post', \App\Models\Post::class);

        Route::model('signup', \App\Models\Signup::class);

        Route::bind('email', function ($value) {
            return app(Registrar::class)->resolveOrFail(['email' => $value]);
        });

        Route::bind('mobile', function ($value) {
            return app(Registrar::class)->resolveOrFail(['mobile' => $value]);
        });

        Route::bind('redirect', function ($key) {
            $redirect = app(Fastly::class)->getRedirect($key);

            if (!$redirect) {
                throw new NotFoundHttpException();
            }

            return $redirect;
        });
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace . '\Web')
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}
