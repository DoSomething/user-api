<?php

namespace App\Auth\Entities;

use Illuminate\Contracts\Auth\Authenticatable;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\UserEntityInterface;

class UserEntity implements UserEntityInterface
{
    use EntityTrait;

    /**
     * Make a new UserEntity from an Eloquent model.
     *
     * @param Authenticatable $user
     * @return self
     */
    public static function fromModel(Authenticatable $user)
    {
        $entity = new self();
        $entity->setIdentifier($user->getAuthIdentifier());

        return $entity;
    }
}
