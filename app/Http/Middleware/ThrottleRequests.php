<?php

namespace Northstar\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottler;

class ThrottleRequests extends BaseThrottler
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  int $maxAttempts
     * @param  int $decayMinutes
     * @return mixed
     */
    public function handle(
        $request,
        Closure $next,
        $maxAttempts = 10,
        $decayMinutes = 15,
        $prefix = ''
    ) {
        if (!config('features.rate-limiting')) {
            return $next($request);
        }

        return parent::handle(
            $request,
            $next,
            $maxAttempts,
            $decayMinutes,
            $prefix,
        );
    }
}
