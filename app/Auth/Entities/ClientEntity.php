<?php

namespace App\Auth\Entities;

use App\Models\Client;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    /**
     * The scopes that this client is allowed to claim.
     * @var array
     */
    protected $allowedScopes;

    /**
     * Make a new OAuth Client entity.
     *
     * @param $client_id
     * @param $client_name
     * @param $scopes
     * @param $redirect_uri
     */
    public function __construct(
        $client_id,
        $client_name,
        $scopes,
        $redirect_uri = ''
    ) {
        $this->identifier = $client_id;
        $this->name = $client_name;
        $this->allowedScopes = $scopes;
        $this->redirectUri = $redirect_uri;
    }

    /**
     * Is this client able to keep a secret?
     *
     * @return bool
     */
    public function isConfidential()
    {
        // TODO: When we add support for PKCE flow, we'll want to
        // swap this to 'true' for clients that support it.
        return true;
    }

    /**
     * Make a new ClientEntity from an Eloquent model.
     *
     * @param Client $client
     * @return ClientEntity
     */
    public static function fromModel(Client $client)
    {
        return new self(
            $client->client_id,
            $client->title,
            $client->scope,
            $client->redirect_uri,
        );
    }

    /**
     * Get the scopes that are allowed for this client.
     *
     * @return array
     */
    public function getAllowedScopes()
    {
        return $this->allowedScopes;
    }

    /**
     * Set the allowed scopes for this client.
     *
     * @param $scopes
     */
    public function setAllowedScopes($scopes)
    {
        $this->allowedScopes = $scopes;
    }
}
