<?php

namespace Northstar\Auth;

use Defuse\Crypto\Key;
use League\OAuth2\Server\CryptTrait;

class Encrypter
{
    use CryptTrait;

    public function __construct()
    {
        $this->setEncryptionKey(
            Key::loadFromAsciiSafeString(config('auth.key')),
        );
    }

    public function decryptData($encryptedData)
    {
        return json_decode($this->decrypt($encryptedData), true);
    }
}
