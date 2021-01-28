<?php

namespace App\Providers;

use App\Models\Post;
use App\Models\Signup;
use App\Models\User;
use App\Observers\PostObserver;
use App\Observers\SignupObserver;
use App\Observers\UserObserver;
use Hashids\Hashids;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
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
        Post::observe(PostObserver::class);
        Signup::observe(SignupObserver::class);

        // Register global view composer.
        View::composer('*', function ($view) {
            $view->with('auth', [
                'id' => auth()->id(),
                'token' => auth()->user() ? auth()->user()->access_token : null,
                'role' => auth()->user() ? auth()->user()->role : 'user',
            ]);
        });

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
        // Configure hashids for non-iterable image URLs:
        $this->app->singleton(Hashids::class, function ($app) {
            return new Hashids(config('app.hashid_key'), 10);
        });

        // Override MongoDB Password provider w/ multi-connection support:
        $this->app->singleton('auth.password', function ($app) {
            return new \App\Auth\PasswordBrokerManager($app);
        });

        $this->app->bind('auth.password.broker', function ($app) {
            return $app->make('auth.password')->broker();
        });
    }
}
