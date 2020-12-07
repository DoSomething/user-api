<?php

namespace App\Http\Middleware;

use App\Auth\Role;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Server\Exception\OAuthServerException;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param array $roles
     * @return mixed
     * @throws OAuthServerException
     * @internal param $role
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // If we're using the 'api' driver, then check if we have an
        // access token with the correct role before continuing:
        if (Auth::getDefaultDriver() === 'api') {
            Role::gate($roles);

            return $next($request);
        }

        // Otherwise, this is a traditional web session. Check if we're
        // logged in & have one of the required roles. If not, throw:
        if (Auth::guest() || !Auth::user()->hasRole(...$roles)) {
            throw new AuthorizationException(
                'You don\'t have the correct role to do that!',
            );
        }

        return $next($request);
    }
}
