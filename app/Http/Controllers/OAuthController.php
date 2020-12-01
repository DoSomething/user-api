<?php

namespace Northstar\Http\Controllers;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Http\Request;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Northstar\Auth\Encrypter;
use Northstar\Events\Throttled;
use Northstar\Http\Transformers\UserInfoTransformer;
use Northstar\Models\RefreshToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class OAuthController extends Controller
{
    /**
     * The OAuth authorization server.
     *
     * @var AuthorizationServer
     */
    protected $oauth;

    /**
     * The authentication factory.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The rate limiter.
     *
     * @var RateLimiter
     */
    protected $limiter;

    /**
     * The encrypter/decrypter.
     * @var Encrypter
     */
    protected $encrypter;

    /**
     * Make a new OAuthController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param AuthorizationServer $oauth
     * @param Auth $auth
     * @param RateLimiter $limiter
     * @param Encrypter $encrypter
     */
    public function __construct(
        AuthorizationServer $oauth,
        Auth $auth,
        RateLimiter $limiter,
        Encrypter $encrypter
    ) {
        $this->oauth = $oauth;
        $this->auth = $auth;
        $this->limiter = $limiter;
        $this->encrypter = $encrypter;

        $this->middleware('auth:api', ['only' => ['info', 'invalidateToken']]);
    }

    /**
     * Show the user info for the authorized user.
     *
     * @deprecated
     * @return \Illuminate\Http\Response
     */
    public function info()
    {
        $user = $this->auth->guard('api')->user();

        return $this->item($user, 200, [], new UserInfoTransformer());
    }

    /**
     * Authenticate a registered user using one of the supported OAuth
     * grants and return token details.
     *
     * @see RFC6749 OAuth 2.0 <https://tools.ietf.org/html/rfc6749>
     *      RFC7519 JSON Web Token <https://tools.ietf.org/html/rfc7519>
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws OAuthServerException
     */
    public function createToken(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) {
        $shouldRateLimit = config('features.rate-limiting');

        // If this IP has given incorrect client credentials too many times, take a break.
        // @see: EventServiceProvider `client.authentication.failed` listener.
        if (
            $shouldRateLimit &&
            $this->limiter->tooManyAttempts(request()->fingerprint(), 10)
        ) {
            event(Throttled::class);

            $minutes = ceil(
                $this->limiter->availableIn(request()->fingerprint()) / 60,
            );
            $pluralizedNoun = $minutes === 1 ? 'minute' : 'minutes';
            $message =
                'Too many attempts. Please try again in ' .
                $minutes .
                ' ' .
                $pluralizedNoun .
                '.';

            throw new OAuthServerException($message, 429, 'rate_limit', 429);
        }

        return $this->oauth->respondToAccessTokenRequest($request, $response);
    }

    /**
     * Invalidate the provided refresh token, preventing it from being used
     * to generate new access tokens in the future. This is roughly equivalent
     * to "logging out" the user.
     *
     * @see RFC7009 OAuth2 Token Revocation <https://tools.ietf.org/html/rfc7009>
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws OAuthServerException
     */
    public function invalidateToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'token_type_hint' => 'in:refresh_token', // Since we cannot revoke access tokens, refuse to try.
        ]);

        try {
            $refreshToken = $this->encrypter->decryptData(
                $request->input('token'),
            );
        } catch (\LogicException $e) {
            // Per RFC7009, invalid tokens do _not_ trigger an error response.
            return $this->respond(
                'That refresh token has been successfully revoked.',
                200,
            );
        }

        // Make sure that the authenticated user is allowed to do this.
        if (
            $this->auth
                ->guard('api')
                ->user()
                ->getAuthIdentifier() !== $refreshToken['user_id']
        ) {
            throw OAuthServerException::accessDenied(
                'That refresh token does not belong to the currently authorized user.',
            );
        }

        $token = RefreshToken::where(
            'token',
            $refreshToken['refresh_token_id'],
        )->first();

        // Delete the refresh token, if it exists.
        if ($token) {
            $token->delete();
        }

        return $this->respond(
            'That refresh token has been successfully revoked.',
            200,
        );
    }
}
