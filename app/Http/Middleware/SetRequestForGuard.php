<?php

namespace Northstar\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;

class SetRequestForGuard
{
    /**
     * Inject dependencies for the ParseOAuthHeader middleware.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        auth()->guard('api')->setRequest($this->request);

        return $next($request);
    }
}
