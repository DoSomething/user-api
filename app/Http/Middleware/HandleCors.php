<?php

namespace App\Http\Middleware;

use Closure;
use Fruitcake\Cors\HandleCors as BaseMiddleware;

class HandleCors extends BaseMiddleware
{
    /**
     * Set a wildcard access control header for the following paths.
     *
     * @var string[]
     */
    protected $stars = ['images/*'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure $next
     */
    public function handle($request, Closure $next)
    {
        // If this request matches any paths in the "stars" array, attach a
        // wildcard allowed origin. This allows pages from *any* domain to
        // embed/access this resource:
        if ($request->is(...$this->stars)) {
            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Origin', '*');

            return $response;
        }

        // Otherwise, we'll default to attaching origin-specific access control
        // headers (e.g. 'Access-Control-Allow-Origin: www.dosomething.org'):
        return parent::handle($request, $next);
    }
}
