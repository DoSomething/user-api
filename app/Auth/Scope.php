<?php

namespace Northstar\Auth;

use League\OAuth2\Server\Exception\OAuthServerException;
use Northstar\Models\Client;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Scope
{
    /**
     * Available API Key scopes.
     * @var array
     */
    protected static $scopes = [
        'role:admin' => [
            'description' => 'Allows this client to act as an administrator if the user has that role.',
        ],
        'role:staff' => [
            'description' => 'Allows this client to act as a staff member if the user has that role.',
        ],
        'admin' => [
            'description' => 'Grant administrative privileges to this token, whether or not the user has the admin role.',
            'warning' => true,
        ],
        'user' => [
            'description' => 'Allows actions to be made on a user\'s behalf.',
        ],
        'profile' => [
            'description' => 'Allows viewing an authenticated user\'s full profile.',
        ],
        'email' => [
            'description' => 'Allows viewing the authenticated user\'s email address.',
        ],
        'openid' => [
            'description' => 'Allows users to be authorized by OpenID Connect.',
        ],
        'client' => [
            'description' => 'Allows access to the client resource.',
        ],
    ];

    /**
     * Return a list of all scopes & their descriptions.
     *
     * @return array
     */
    public static function all()
    {
        return static::$scopes;
    }

    /**
     * Validate if all the given scopes are valid.
     *
     * @param $scopes
     * @return bool
     */
    public static function validateScopes($scopes)
    {
        if (! is_array($scopes)) {
            return false;
        }

        return ! array_diff($scopes, array_keys(static::$scopes));
    }

    /**
     * Return whether the current request includes the proper client scopes.
     *
     * @param $scope - Required scope
     * @return bool
     */
    public static function allows($scope)
    {
        // If trying to check `role:user`, check `user` scope instead.
        // @TODO: Change this scope so it's consistent.
        if ($scope === 'role:user') {
            $scope = 'user';
        }

        $oauthScopes = request()->attributes->get('oauth_scopes');

        // If scopes have been parsed from a provided JWT access token, check against
        // those. Otherwise, check the Client specified by the `X-DS-REST-API-Key` header.
        if (! is_null($oauthScopes)) {
            return in_array($scope, $oauthScopes);
        }

        // Otherwise, try to get the client from the legacy X-DS-REST-API-Key header,
        // and compare against its whitelisted scopes.
        $client_secret = request()->header('X-DS-REST-API-Key');
        $client = Client::where('client_secret', $client_secret)->first();

        return $client && in_array($scope, $client->scope);
    }

    /**
     * Throw an exception if a properly scoped API key is not
     * provided with the current request.
     *
     * @param $scope - Required scope
     * @throws OAuthServerException
     */
    public static function gate($scope)
    {
        if (has_middleware('web')) {
            return true;
        }

        if (! static::allows($scope)) {
            app('stathat')->ezCount('invalid client scope error');

            // If scopes have been parsed from a provided JWT access token or we are looking at a v2 endpoint,
            // use OAuth access denied exception to return a 401 error.
            if (request()->attributes->has('oauth_scopes') || request()->route()->getPrefix() === '/v2') {
                throw OAuthServerException::accessDenied('Requires the `'.$scope.'` scope.');
            }

            // ...if we're using a legacy API key, return the expected 403 error.
            throw new AccessDeniedHttpException('You must be using an API key with "'.$scope.'" scope to do that.');
        }
    }
}
