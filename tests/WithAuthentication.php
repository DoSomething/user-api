<?php

namespace Tests;

use DateInterval;
use League\OAuth2\Server\CryptKey;
use Northstar\Auth\Entities\AccessTokenEntity;
use Northstar\Auth\Entities\ClientEntity;
use Northstar\Auth\Entities\ScopeEntity;
use Northstar\Auth\Scope;
use Northstar\Models\Client;
use Northstar\Models\User;

trait WithAuthentication
{
    /**
     * Make a new authenticated web user.
     *
     * @return \Northstar\Models\User
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
            'client_id' => 'testing'.$this->faker->uuid,
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

        return $this->asUser($user, ['user']);
    }

    /**
     * Make the following request as a staff user with the `user` and `role:staff` scopes.
     *
     * @return $this
     */
    public function asStaffUser()
    {
        $staff = factory(User::class, 'staff')->create();

        return $this->asUser($staff, ['user', 'role:staff']);
    }

    /**
     * Make the following request as an admin user with the `user`, `client`, and `role:admin` scopes.
     *
     * @return $this
     */
    public function asAdminUser()
    {
        $admin = factory(User::class, 'admin')->create();

        return $this->asUser($admin, ['user', 'client', 'role:admin']);
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
        $accessToken->setClient(new ClientEntity('phpunit', 'PHPUnit', $scopes));
        $accessToken->setIdentifier(bin2hex(random_bytes(40)));
        $accessToken->setExpiryDateTime((new \DateTime())->add(new DateInterval('PT1H')));

        if ($user) {
            $accessToken->setUserIdentifier($user->id);
            $accessToken->setRole($user->role);
        }

        foreach ($scopes as $identifier) {
            if (! array_key_exists($identifier, Scope::all())) {
                continue;
            }

            $entity = new ScopeEntity();
            $entity->setIdentifier($identifier);
            $accessToken->addScope($entity);
        }

        $header = 'Bearer '.$accessToken->convertToJWT(new CryptKey(storage_path('keys/private.key'), null, false));
        $this->serverVariables = array_replace($this->serverVariables, [
            'HTTP_Authorization' => $header,
        ]);

        return $this;
    }

    /**
     * Create a signed JWT to authorize resource requests.
     *
     * @param User $user
     * @param array $scopes
     * @return $this
     */
    public function asUser($user, $scopes = [])
    {
        return $this->withAccessToken($scopes, $user);
    }
}
