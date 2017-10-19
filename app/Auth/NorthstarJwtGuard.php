<?php

namespace Northstar\Auth;

use Illuminate\Auth\GuardHelpers;
use InvalidArgumentException;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\ValidationData;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Guard;

class NorthstarJwtGuard implements Guard
{
    use GuardHelpers;

    /**
     * The name of the guard.
     *
     * @var string
     */
    protected $name;

    /**
     * The user we last attempted to retrieve.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    protected $lastAttempted;

    /**
     * Indicates if the user was authenticated via a recaller cookie.
     *
     * @var bool
     */
    protected $viaRemember = false;

    /**
     * The public key used to validate tokens.
     *
     * @var CryptKey
     */
    protected $key;

    /**
     * The request instance.
     *
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * The Illuminate cookie creator service.
     *
     * @var \Illuminate\Contracts\Cookie\QueueingFactory
     */
    protected $cookie;

    /**
     * Indicates if a token refresh has been attempted.
     *
     * @var bool
     */
    protected $tokenRefreshAttempted = false;

    /**
     * Create a new authentication guard.
     *
     * @param $name
     * @param \League\OAuth2\Server\CryptKey $key
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \Illuminate\Http\Request $request
     */
    public function __construct($name, CryptKey $key, UserProvider $provider, Request $request = null)
    {
        $this->name = $name;
        $this->key = $key;
        $this->request = $request;
        $this->provider = $provider;
    }

    /**
     * Get the "realm" to check a cookie for. This allows us to store
     * separate cookies for QA instances and production instances on
     * the same base domain.
     *
     * @return string
     */
    protected function getRealm()
    {
        // @TODO: This should be customizable.
        return 'northstar';
    }

    /**
     * @param Request $request
     * @return string|null
     */
    protected function getToken(Request $request) {
        // If there's an `Authorization` header, use that.
        if ($request->hasHeader('authorization')) {
            $header = $request->header('authorization');
            return trim(preg_replace('/^(?:\s+)?Bearer\s/', '', $header[0]));
        }

        $cookieName = $this->getRealm() . '_token';
        if ($request->hasCookie($cookieName)) {
            return $request->cookie($cookieName);
        }

        return null;
    }

    /**
     * Parse the JWT from cookie or Authorization header.
     *
     * @param  string $jwt
     * @return \Lcobucci\JWT\Token
     * @throws OAuthServerException
     */
    protected function parseToken(string $jwt)
    {
        // Attempt to parse and validate the JWT
        $token = (new Parser())->parse($jwt);
        if ($token->verify(new Sha256(), $this->key->getKeyPath()) === false) {
            throw OAuthServerException::accessDenied('Access token could not be verified');
        }

        // Ensure access token hasn't expired
        $data = new ValidationData();
        $data->setCurrentTime(time());

        if ($token->validate($data) === false) {
            throw OAuthServerException::accessDenied('Access token is invalid');
        }

        return $token;
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately rather than re-parsing the JWT.
        if (! is_null($this->user)) {
            return $this->user;
        }

        // Read the JWT from either the `Authorization` header or a cookie,
        // and then parse it to see if it's valid for use.
        $jwt = $this->getToken($this->request);
        $token = $this->parseToken($jwt);

        if (is_null($token)) {
            return null;
        }

        $id = $token->getClaim('sub');
        $user = null;

        if (! is_null($id)) {
            $user = $this->provider->retrieveById($id);
        }

        // If the user is null, but we have a "refresh token" cookie we can attempt to
        // use that to request a new access token from the authorization server.
        $refreshToken = $this->getRefreshToken($this->request);
        if (is_null($user) && ! is_null($refreshToken)) {
            $token = $this->useRefreshToken($refreshToken);
            $user = $token->getClaim('sub');

            if ($user) {
                $this->updateSession($user->getAuthIdentifier());
            }
        }

        return $this->user = $user;
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        $id = $this->session->get($this->getName());

        if (is_null($id) && $this->user()) {
            $id = $this->user()->getAuthIdentifier();
        }

        return $id;
    }

    /**
     * Pull a user from the repository by its recaller ID.
     *
     * @param  string  $refreshToken
     * @return \Lcobucci\JWT\Token
     */
    protected function useRefreshToken($refreshToken)
    {
        $isValidRefreshToken = true; // @TODO

        if ($isValidRefreshToken && ! $this->tokenRefreshAttempted) {
            $this->tokenRefreshAttempted = true;

            // @TODO: !!
        }

        return null;
    }

    /**
     * Log the given user ID into the application without sessions or cookies.
     *
     * @param  mixed  $id
     * @return bool
     */
    public function onceUsingId($id)
    {
        $user = $this->provider->retrieveById($id);

        if (! is_null($user)) {
            $this->setUser($user);

            return true;
        }

        return false;
    }

    /**
     * Log the user out of the application.
     *
     * @return void
     */
    public function logout()
    {
        if (! is_null($this->user)) {
            $this->revokeRefreshToken($this->user);
        }

        $this->clearUserDataFromStorage();

        $this->user = null;
    }

    /**
     * @param  Request $request
     * @return null
     */
    protected function getRefreshToken($request)
    {
        $cookieName = $this->getRealm() . '_refresh_token';
        if ($request->hasCookie($cookieName)) {
            return $request->cookie($cookieName);
        }

        return null;
    }

    protected function clearUserDataFromStorage()
    {
        //
    }

    protected function revokeRefreshToken($user)
    {
        //
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function once(array $credentials = [])
    {
        throw new InvalidArgumentException('Auth::once is not supported with Gatekeeper.');
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        throw new InvalidArgumentException('Auth::validate is not supported with Gatekeeper.');
    }
}
