<?php

namespace Northstar\Auth\Repositories;

use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Northstar\Auth\Entities\RefreshTokenEntity;
use Northstar\Models\RefreshToken;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * Creates a new refresh token
     *
     * @return RefreshTokenEntityInterface
     */
    public function getNewRefreshToken()
    {
        return new RefreshTokenEntity();
    }

    /**
     * Create a new refresh token_name.
     *
     * @param \League\OAuth2\Server\Entities\RefreshTokenEntityInterface $refreshTokenEntity
     */
    public function persistNewRefreshToken(
        RefreshTokenEntityInterface $refreshTokenEntity
    ) {
        RefreshToken::create([
            'token' => $refreshTokenEntity->getIdentifier(),
            'scopes' => $refreshTokenEntity->getAccessToken()->getScopes(),
            'user_id' => $refreshTokenEntity
                ->getAccessToken()
                ->getUserIdentifier(),
            'client_id' => $refreshTokenEntity
                ->getAccessToken()
                ->getClient()
                ->getIdentifier(),
        ]);
    }

    /**
     * Revoke the refresh token.
     *
     * @param string $tokenId
     */
    public function revokeRefreshToken($tokenId)
    {
        $model = RefreshToken::where('token', $tokenId)->first();

        if ($model) {
            $model->delete();
        }
    }

    /**
     * Check if the refresh token has been revoked.
     *
     * @param string $tokenId
     * @return bool
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        $exists = RefreshToken::where('token', $tokenId)->exists();

        return !$exists;
    }
}
