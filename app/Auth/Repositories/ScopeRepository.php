<?php

namespace App\Auth\Repositories;

use App\Auth\Entities\ScopeEntity;
use App\Auth\Scope;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;

class ScopeRepository implements ScopeRepositoryInterface
{
    /**
     * Create ScopeEntities from a given list of identifiers.
     *
     * @return ScopeEntityInterface[]
     */
    public function create(...$identifiers)
    {
        $scopeEntities = array_map(function ($identifier) {
            return $this->getScopeEntityByIdentifier($identifier);
        }, $identifiers);

        return array_filter($scopeEntities);
    }

    /**
     * Return information about a scope.
     *
     * @param string $identifier The scope identifier
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface
     */
    public function getScopeEntityByIdentifier($identifier)
    {
        $scopes = Scope::all();

        if (array_key_exists($identifier, $scopes) === false) {
            return null;
        }

        $entity = new ScopeEntity();
        $entity->setIdentifier($identifier);

        return $entity;
    }

    /**
     * Given a client, grant type, and optional user identifier validate the set of requested scopes
     * are valid and optionally append additional scopes or remove requested scopes.
     *
     * @param ScopeEntityInterface[] $scopes
     * @param string $grantType
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @param null|string $userIdentifier
     * @return \League\OAuth2\Server\Entities\ScopeEntityInterface[]
     */
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        $allowedScopes = $clientEntity->getAllowedScopes();
        $filteredScopes = array_filter($scopes, function (
            ScopeEntity $scope
        ) use ($allowedScopes) {
            return in_array($scope->getIdentifier(), $allowedScopes);
        });

        return $filteredScopes;
    }
}
