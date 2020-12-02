<?php

namespace App\Auth\Repositories;

use App\Auth\Entities\AccessTokenEntity;
use App\Models\User;
use Carbon\Carbon;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * Create a new access token.
     *
     * @param \League\OAuth2\Server\Entities\ClientEntityInterface $clientEntity
     * @param \League\OAuth2\Server\Entities\ScopeEntityInterface[] $scopes
     * @param mixed $userIdentifier
     * @return AccessTokenEntityInterface
     */
    public function getNewToken(
        ClientEntityInterface $clientEntity,
        array $scopes,
        $userIdentifier = null
    ) {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        if ($userIdentifier) {
            $user = User::find($userIdentifier);

            // Update the user's "last accessed at" timestamp.
            $user->last_accessed_at = Carbon::now();
            $user->save();

            // Embed the user's information in the token.
            $accessToken->setUserIdentifier($userIdentifier);
            $accessToken->setRole($user->role);
        }

        return $accessToken;
    }

    /**
     * Persists a new access token to permanent storage.
     *
     * @param \League\OAuth2\Server\Entities\AccessTokenEntityInterface $accessTokenEntity
     */
    public function persistNewAccessToken(
        AccessTokenEntityInterface $accessTokenEntity
    ) {
        // Since access tokens are not checked against the database, but instead
        // verified by their hash, we'll just make a record in the log.
        logger('issued access token', [
            'id' => $accessTokenEntity->getUserIdentifier(),
            'jti' => $accessTokenEntity->getIdentifier(),
        ]);
    }

    /**
     * Revoke an access token.
     *
     * @param string $tokenId
     */
    public function revokeAccessToken($tokenId)
    {
        // Access tokens cannot be revoked, since their authenticity is never
        // verified against the database. Instead, revoke the corresponding
        // refresh token so another JWT cannot be created.
    }

    /**
     * Access tokens cannot be revoked.
     *
     * @param string $tokenId
     * @return bool Return true if this token has been revoked
     */
    public function isAccessTokenRevoked($tokenId)
    {
        return false;
    }
}
