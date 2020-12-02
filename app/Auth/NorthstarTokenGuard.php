<?php

namespace Northstar\Auth;

use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Mockery\CountValidator\Exception;
use Northstar\Models\User;

class NorthstarTokenGuard extends TokenGuard implements Guard
{
    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        parent::__construct($provider, $request);

        $this->inputKey = 'token';
        $this->storageKey = 'key';
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        // If we've already retrieved the user for the current request we can just
        // return it back immediately. We do not want to fetch the user data on
        // every call to this method because that would be tremendously slow.
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        $user = $this->getUserForToken();

        return $this->user = $user;
    }

    /**
     * Retrieve the user from a parsed JWT access token that
     * was provided with the current request.
     *
     * @return \Northstar\Models\User|null
     */
    public function getUserForToken()
    {
        $id = request()->attributes->get('oauth_user_id');

        if (empty($id)) {
            return null;
        }

        return User::find($id);
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        return $this->provider->validateCredentials($user, $credentials);
    }

    /**
     * Log a user into the application without sessions or cookies.
     *
     * @throws Exception
     */
    public function once()
    {
        throw new Exception(
            'Token-based authentication is stateless. Use Auth::check instead.',
        );
    }
}
