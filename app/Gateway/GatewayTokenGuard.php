<?php

namespace Northstar\Gateway;

use Exception;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Str;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\ResourceServer;
use Psr\Http\Message\ServerRequestInterface;

class GatewayTokenGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * The resource server.
     *
     * @var ResourceServer
     */
    protected $oauth;

    /**
     * The access token for the current request.
     *
     * @var string
     */
    protected $token = null;

    /**
     * The scopes granted to the current request.
     *
     * @var array
     */
    protected $scopes = null;

    /**
     * The OAuth client used to make the current request.
     *
     * @var string
     */
    protected $client = null;

    /**
     * The user role for the current request.
     *
     * @var string
     */
    protected $role = null;

    /**
     * Create a new authentication guard.
     *
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \League\OAuth2\Server\ResourceServer $oauth
     */
    public function __construct(UserProvider $provider, ResourceServer $oauth)
    {
        $this->provider = $provider;
        $this->oauth = $oauth;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (is_null($this->user)) {
            $this->parseToken();
        }

        return $this->user;
    }

    /**
     * Get the access token for the current request.
     *
     * @return string
     */
    public function token() {
        if (is_null($this->token)) {
            $this->parseToken();
        }

        return $this->token;
    }

    /**
     * Get the access token for the current request.
     *
     * @return string
     */
    public function client() {
        if (is_null($this->client)) {
            $this->parseToken();
        }

        return $this->client;
    }

    /**
     * Get or check the scopes granted to the current request.
     *
     * @param string|null $scope
     * @return array|bool
     */
    public function scopes($scope = null)
    {
        if (is_null($this->scopes)) {
            $this->parseToken();
        }

        // If a scope is provided, check for presence.
        if (! is_null($scope)) {
            $scopes = ! empty($this->scopes) ? $this->scopes : [];

            return in_array($scope, $scopes);
        }

        return is_null($this->scopes) ? [] : $this->scopes;
    }

    /**
     * Get or check the role attached to the current request.
     *
     * @param string|null $role
     * @return array|bool
     */
    public function role($role = null)
    {
        if (is_null($this->role)) {
            $this->parseToken();
        }

        // If a scope is provided, check for presence.
        if (! is_null($role)) {
            return $role === $this->role;
        }

        return $this->role;
    }

    /**
     * Get the token for the current request.
     *
     * @return void
     */
    protected function parseToken()
    {
        $header = $this->request->getHeader('authorization');

        if (! empty($header) && $this->isBearerToken($header[0])) {
            $validatedRequest = $this->oauth->validateAuthenticatedRequest($this->request);

            // Save the parsed attributes (oauth_access_token_id, oauth_client_id,
            // oauth_user_id, & oauth_scopes) for later use during this request.
            $this->token = $validatedRequest->getAttribute('oauth_access_token_id');
            $this->client = $validatedRequest->getAttribute('oauth_client_id');
            $this->scopes = $validatedRequest->getAttribute('oauth_scopes', []);

            $userId = $validatedRequest->getAttribute('oauth_user_id');
            $this->user = $this->provider->retrieveById($userId);

            // @TODO: We shouldn't need to re-parse this for a custom field...
            $jwt = (new Parser)->parse(Str::substr($header[0], 7));
            $this->role = $jwt->getClaim('role');
        }
    }

    /**
     * Is the given token a Bearer token?
     *
     * @param string $header
     * @return bool
     */
    public function isBearerToken($header)
    {
        return Str::startsWith($header, 'Bearer ');
    }

    /**
     * Validate a the given token (credentials).
     *
     * @param  array $credentials
     * @throws Exception
     * @return void
     */
    public function validate(array $credentials = [])
    {
        throw new Exception('no.');
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @throws Exception
     */
    public function once()
    {
        throw new Exception('Token-based authentication is stateless. Use Auth::check instead.');
    }

    /**
     * Set the current request instance.
     *
     * @param ServerRequestInterface $request
     * @return $this
     */
    public function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }
}

