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
            'description' => 'Allows application to use a admin user\'s permissions.',
        ],
        'role:staff' => [
            'description' => 'Allows application to use a staff user\'s permissions.',
        ],
        'admin' => [
            'description' => 'Grant administrative privileges to this token, whether or not the user has the admin role.',
            'hint' => 'Careful, don\'t use this scope with Authorization Code clients!',
        ],
        'user' => [
            'description' => 'Allows access to the user resource.',
        ],
        'client' => [
            'description' => 'Allows access to the client resource.',
            'hint' => 'Be sure you need this scope before assigning! This allows your app to read other OAuth client secrets, so a leaked key is extra dangerous.',
        ],
        'activity' => [
            'description' => 'Allows access to user activity resources (signups, posts, etc.) in Rogue.',
        ],
        'write' => [
            'description' => 'Allows access to create/update/delete endpoints.',
        ],
        'profile' => [
            'description' => 'Some third-party implementations may request this scope.',
        ],
        'email' => [
            'description' => 'Some third-party implementations may request this scope.',
        ],
        'openid' => [
            'description' => 'Some third-party implementations may request this scope.',
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
        // Only check scopes if request is made with OAuth or legacy header.
        $hasAccessToken = request()->attributes->has('oauth_access_token_id');
        $hasLegacyAuthHeader = request()->headers->has('x-ds-rest-api-key');
        $shouldCheckScopes = $hasAccessToken || $hasLegacyAuthHeader;
        if (! $shouldCheckScopes) {
            return;
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
