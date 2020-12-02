<?php

namespace App\Http\Middleware;

use App\Auth\Role;
use Closure;
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
        Role::gate($roles);

        return $next($request);
    }
}
