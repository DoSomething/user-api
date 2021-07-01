<?php

namespace Tests;

use App\Auth\Entities\AccessTokenEntity;
use App\Auth\Entities\ClientEntity;
use App\Auth\Entities\ScopeEntity;
use App\Auth\Scope;
use App\Models\Client;
use App\Models\User;
use DateInterval;
use League\OAuth2\Server\CryptKey;

trait WithAuthentication
{
    /**
     * Make a new authenticated web user.
     *
     * @return \App\Models\User
     */
    protected function makeAuthWebUser()
    {
        $user = factory(User::class)->create();

        $this->be($user, 'web');

        return $user;
    }

    /**
     * Use the given API key for this request.
     *
     * @param Client $client
     * @return $this
     */
    public function withLegacyApiKey(Client $client)
    {
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_X-DS-REST-API-Key' => $client->client_secret,
        ]);

        return $this;
    }

    /**
     * Set an API key with the given scopes on the request.
     *
     * @param array $scopes
     * @return $this
     */
    public function withLegacyApiKeyScopes(array $scopes)
    {
        $client = Client::create([
            'client_id' => 'testing' . $this->faker->uuid,
            'scope' => $scopes,
        ]);

        $this->withLegacyApiKey($client);

        return $this;
    }

    /**
     * Make the following request as a normal user with the `user` scope.
     *
     * @return $this
     */
    public function asNormalUser()
    {
        $user = factory(User::class)->create();

        return $this->asUser($user);
    }

    /**
     * Make the following request as a staff user with the `user` and `role:staff` scopes.
     *
     * @return $this
     */
    public function asStaffUser()
    {
        $staff = factory(User::class)->states('staff')->create();

        return $this->asUser($staff);
    }

    /**
     * Make the following request as an admin user with the `user`, `client`, and `role:admin` scopes.
     *
     * @return $this
     */
    public function asAdminUser()
    {
        $admin = factory(User::class)->states('admin')->create();

        return $this->asUser($admin);
    }

    /**
     * Create a signed JWT to authorize resource requests.
     *
     * @param User $user
     * @param array $scopes
     * @return $this
     */
    public function withAccessToken($scopes = [], $user = null)
    {
        $accessToken = new AccessTokenEntity();

        $accessToken->setClient(
            new ClientEntity('phpunit', 'PHPUnit', $scopes),
        );

        $accessToken->setIdentifier(bin2hex(random_bytes(40)));

        $accessToken->setExpiryDateTime(
            (new \DateTimeImmutable())->add(new DateInterval('PT1H')),
        );

        $accessToken->setPrivateKey(
            new CryptKey(storage_path('app/keys/private.key'), null, false),
        );

        if ($user) {
            $accessToken->setUserIdentifier($user->id);
            $accessToken->setRole($user->role);
        }

        foreach ($scopes as $identifier) {
            if (!array_key_exists($identifier, Scope::all())) {
                continue;
            }

            $entity = new ScopeEntity();
            $entity->setIdentifier($identifier);
            $accessToken->addScope($entity);
        }

        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_Authorization' => 'Bearer ' . $accessToken,
        ]);

        return $this;
    }

    /**
     * Create a signed JWT to authorize resource requests.
     *
     * @return $this
     */
    public function asMachine()
    {
        return $this->withAccessToken(['admin', 'user', 'write']);
    }

    /**
     * Create a signed JWT to authorize resource requests.
     *
     * @param User $user
     * @param array $scopes
     * @return $this
     */
    public function asUser(
        $user,
        $scopes = [
            'user',
            'activity',
            'client',
            'role:staff',
            'role:admin',
            'write',
        ]
    ) {
        return $this->withAccessToken($scopes, $user);
    }
}
