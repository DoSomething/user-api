<?php

namespace App\Http\Middleware;

use Illuminate\Support\Str;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;

class ParseOAuthHeader
{
    /**
     * Inject dependencies for the ParseOAuthHeader middleware.
     *
     * @param ResourceServer $oauth
     * @param ServerRequestInterface $request
     */
    public function __construct(
        ResourceServer $oauth,
        ServerRequestInterface $request
    ) {
        $this->oauth = $oauth;
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
        $header = $request->header('Authorization', '');

        // Only attempt to parse as a JWT if Bearer token & not a legacy database token.
        if ($this->isBearerToken($header) && !$this->isLegacyToken($header)) {
            $this->request = $this->oauth->validateAuthenticatedRequest(
                $this->request,
            );

            // Add the parsed attributes (oauth_access_token_id, oauth_client_id,
            // oauth_user_id, & oauth_scopes) to the request.
            $request->attributes->add($this->request->getAttributes());
        }

        return $next($request);
    }

    /**
     * Is the given token a Bearer token?
     *
     * @return bool
     */
    public function isBearerToken($header)
    {
        return Str::startsWith($header, 'Bearer ');
    }

    /**
     * Is this a legacy database token? If so, we don't want to try to
     * parse it as a JWT access token.
     *
     * @param $header
     * @return bool
     */
    public function isLegacyToken($header)
    {
        // A legacy key is always 32 characters (with the "Bearer " prefix = 39 characters)...
        return strlen($header) === 39;
    }
}
