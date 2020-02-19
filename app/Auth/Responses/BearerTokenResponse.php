<?php

namespace Northstar\Auth\Responses;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse as BaseTokenResponse;

class BearerTokenResponse extends BaseTokenResponse
{
    /**
     * Add custom fields to your Bearer Token response here.
     *
     * @param AccessTokenEntityInterface $accessToken
     *
     * @return array
     */
    protected function getExtraParams(AccessTokenEntityInterface $accessToken)
    {
        return ['id_token' => (string) $this->accessToken];
    }
}
