<?php

namespace Northstar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Contracts\Auth\Factory as Auth;

class RedirectIfAuthenticated
{
    /**
     * The authentication factory.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'api')
    {
        if ($this->auth->guard($guard)->check()) {
            return $this->handleAuthenticated($request);
        }

        return $next($request);
    }

    /**
     * Handle an anonymous request.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function handleAuthenticated(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            throw new HttpException(
                401,
                'You cannot do this with an active authentication token.',
            );
        }

        return redirect('/');
    }
}
