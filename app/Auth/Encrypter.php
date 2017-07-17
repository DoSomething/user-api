<?php

namespace Northstar\Auth;

use League\OAuth2\Server\CryptTrait;

class Encrypter
{
    use CryptTrait;

    public function __construct()
    {
        $this->setEncryptionKey(config('app.key'));
    }

    public function decryptData($encryptedData)
    {
        return json_decode($this->decrypt($encryptedData), true);
    }
}
