<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;

class AuthenticateWithApiKey
{
    /**
     * Valid API key headers & secrets.
     *
     * @var array
     */
    protected array $keys;

    public function __construct()
    {
        $this->keys = [
            'X-DS-CallPower-API-Key' => config('auth.partners.callpower'),
            'X-DS-SoftEdge-API-Key' => config('auth.partners.softedge'),
        ];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  $headers
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$headers)
    {
        // Check the header(s) for authorizing this route. If any match,
        // we're good! If not, tell this request to hit the road.
        foreach ($headers as $header) {
            // Ensure that if we haven't set an environment variable for this
            // environment that we throw an error rather than leave these ungated:
            if (empty($this->keys[$header])) {
                throw new \Exception(
                    "The secret for '$header' cannot be empty.",
                );
            }

            if ($request->header($header) === $this->keys[$header]) {
                return $next($request);
            }
        }

        throw new AuthenticationException();
    }
}
