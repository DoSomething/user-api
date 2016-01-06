<?php

namespace Northstar\Http\Middleware;

use Northstar\Models\ApiKey;
use Closure;

class AuthenticateAPIKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string $scope
     * @return mixed
     */
    public function handle($request, Closure $next, $scope = 'user')
    {
        $app_id = $request->header('X-DS-Application-Id');
        $api_key = $request->header('X-DS-REST-API-Key');

        $key = ApiKey::get($app_id, $api_key);

        if (! $key) {
            return response()->json('Unauthorized access.', 401);
        }

        if (! $key->hasScope($scope)) {
            return response()->json('API key is missing required scope.', 403);
        }

        return $next($request);
    }
}
