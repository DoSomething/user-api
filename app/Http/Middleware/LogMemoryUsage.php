<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class LogMemoryUsage
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
        $response = $next($request);

        if (!config('features.hide-memory-usage-log')) {
            // Log how much memory this request used.
            $megabytes = memory_get_peak_usage() / 1000000;
            Log::debug('memory_usage', [
                'mb' => $megabytes,
                'path' => $request->path(),
            ]);       
        }

        return $response;
    }
}
