<?php

namespace Northstar\Auth;

use League\OAuth2\Server\Exception\OAuthServerException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Role
{
    /**
     * Available roles.
     * @var array
     */
    protected static $roles = [
        'admin' => [
            'description' => 'This user is an administrator.',
        ],
        'staff' => [
            'description' => 'This user is a staff member.',
        ],
        'user' => [
            'description' => 'Default user permissions. This is a normal member.',
        ],
    ];

    /**
     * Return a list of all roles & their descriptions.
     *
     * @return array
     */
    public static function all()
    {
        return static::$roles;
    }

    /**
     * Validate if the given role is valid.
     *
     * @param $role
     * @return bool
     */
    public static function validateRole($role)
    {
        return in_array($role, array_keys(static::$roles));
    }

    /**
     * Return whether the user for the current request has the right role.
     *
     * @param array $allowedRoles
     * @return bool
     */
    public static function allows(array $allowedRoles)
    {
        // The 'admin' scope should grant the active user the admin role.
        if (in_array('admin', $allowedRoles) && Scope::allows('admin')) {
            return true;
        }

        $role = auth()->role();

        // If there isn't a logged-in user, they can't have a role!
        if (! $role) {
            return false;
        }

        // Check that the client is allowed to act as this role.
        Scope::gate('role:'.$role);

        return in_array($role, $allowedRoles);
    }

    /**
     * Throw an exception if a properly scoped API key is not
     * provided with the current request.
     *
     * @param array $allowedRoles
     * @throws OAuthServerException
     */
    public static function gate(array $allowedRoles)
    {
        if (! static::allows($allowedRoles)) {
            app('stathat')->ezCount('invalid role error');

            // If request is authenticated by a JWT access token or we are looking at a v2 endpoint,
            // use OAuth access denied exception to return a 401 error.
            if (auth()->guard('api')->token() || request()->route()->getPrefix() === '/v2') {
                throw OAuthServerException::accessDenied('Requires one of the following roles: `'.implode(', ', $allowedRoles).'.');
            }

            // ...if we're using a legacy API key, return the expected 403 error.
            throw new AccessDeniedHttpException('Requires one of the following roles: `'.implode(', ', $allowedRoles).'.');
        }
    }
}
