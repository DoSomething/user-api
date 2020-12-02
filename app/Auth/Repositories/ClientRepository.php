<?php

namespace App\Auth\Repositories;

use App\Auth\Entities\ClientEntity;
use App\Models\Client;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * Get a client.
     *
     * @param string      $clientIdentifier   The client's identifier
     *
     * @return \League\OAuth2\Server\Entities\ClientEntityInterface
     */
    public function getClientEntity($clientIdentifier)
    {
        /** @var \App\Models\Client $model */
        $model = Client::where('client_id', $clientIdentifier)->first();

        if (!$model) {
            return null;
        }

        return ClientEntity::fromModel($model);
    }

    /**
     * Validate a client's secret.
     *
     * @param string      $clientIdentifier The client's identifier
     * @param null|string $clientSecret     The client's secret (if sent)
     * @param null|string $grantType        The type of grant the client is using (if sent)
     *
     * @return bool
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        $client = Client::where('client_id', $clientIdentifier)->first();

        if (!$client) {
            return false;
        }

        if (!$this->clientCanUseGrant($client, $grantType)) {
            return false;
        }

        return $client->client_secret === $clientSecret;
    }

    /**
     * Is the given client allowed to use the given grant type?
     *
     * @param $client
     * @param $grantType
     * @return bool
     */
    public function clientCanUseGrant($client, $grantType)
    {
        // The refresh token grant can be used by password or auth code tokens.
        if ($grantType === 'refresh_token') {
            return in_array($client->allowed_grant, [
                'password',
                'authorization_code',
            ]);
        }

        // Otherwise, the client must always match the grant being used.
        return $client->allowed_grant === $grantType;
    }
}
