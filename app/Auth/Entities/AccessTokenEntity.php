<?php

namespace App\Auth\Entities;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\TokenEntityTrait;

class AccessTokenEntity implements AccessTokenEntityInterface
{
    use EntityTrait, AccessTokenTrait, TokenEntityTrait;

    /**
     * The user's role.
     *
     * @var string
     */
    protected $role = '';

    /**
     * Get the role for the user that the token was issued to.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set the role for the user that the token was issued to.
     *
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Generate a JWT from the access token. We override this method from
     * AccessTokenTrait so that we can add the `iss` and `role` claims.
     *
     * @param CryptKey $privateKey
     * @return Token
     */
    public function convertToJWT(CryptKey $privateKey)
    {
        return (new Builder())
            ->setIssuer(url('/'))
            ->setAudience($this->getClient()->getIdentifier())
            ->setId($this->getIdentifier(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time())
            ->setExpiration($this->getExpiryDateTime()->getTimestamp())
            ->setSubject($this->getUserIdentifier())
            ->set('role', $this->getRole())
            ->set('scopes', $this->getScopes())
            ->sign(
                new Sha256(),
                new Key(
                    $privateKey->getKeyPath(),
                    $privateKey->getPassPhrase(),
                ),
            )
            ->getToken();
    }
}
