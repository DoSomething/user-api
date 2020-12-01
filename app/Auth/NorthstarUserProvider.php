<?php

namespace Northstar\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;

class NorthstarUserProvider extends EloquentUserProvider implements UserProvider
{
    /**
     * The registrar handles retrieving users from the database.
     * @var Registrar
     */
    protected $registrar;

    /**
     * Create a new database user provider.
     *
     * @param \Northstar\Auth\Registrar $registrar
     * @param \Illuminate\Contracts\Hashing\Hasher $hasher
     * @param string $model
     */
    public function __construct(
        Registrar $registrar,
        HasherContract $hasher,
        $model
    ) {
        $this->registrar = $registrar;

        parent::__construct($hasher, $model);
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Northstar\Models\User|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        return $this->registrar->resolve($credentials);
    }

    /**
     * Validate a user against the given credentials. If the user has a Drupal
     * password & it matches, re-hash and save to the user document.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(UserContract $user, array $credentials)
    {
        return $this->registrar->validateCredentials($user, $credentials);
    }
}
