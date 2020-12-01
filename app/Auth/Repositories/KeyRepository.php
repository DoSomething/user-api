<?php

namespace Northstar\Auth\Repositories;

use Storage;
use League\OAuth2\Server\CryptKey;

class KeyRepository
{
    /**
     * Check to see if any keys are stored.
     *
     * @return bool
     */
    public function hasKeys()
    {
        return Storage::has('keys/public.key') ||
            Storage::has('keys/private.key');
    }

    /**
     * Get the public key.
     *
     * @return \League\OAuth2\Server\CryptKey
     */
    public function getPublicKey()
    {
        return $this->getFileCached('keys/public.key');
    }

    /**
     * Add the public key. Will not overwrite an existing public key.
     *
     * @param string $key
     * @return void
     */
    public function writePublicKey($key)
    {
        Storage::write('keys/public.key', $key);
    }

    /**
     * Get the private key.
     *
     * @return \League\OAuth2\Server\CryptKey
     */
    public function getPrivateKey()
    {
        return $this->getFileCached('keys/private.key');
    }

    /**
     * Add the private key. Will not overwrite an existing private key.
     *
     * @param string $key
     * @return void
     */
    public function writePrivateKey($key)
    {
        Storage::write('keys/private.key', $key);
    }

    /**
     * See if the key file is cached. If not, get from storage and cache.
     *
     * @param string $path
     * @return \League\OAuth2\Server\CryptKey
     */
    protected function getFileCached($path)
    {
        $cachedPath = storage_path('cache/' . $path);

        // If this isn't cached locally, fetch it from storage driver.
        if (!file_exists($cachedPath)) {
            if (!file_exists(storage_path('cache/keys'))) {
                mkdir(storage_path('cache/keys'), 0770, true);
            }

            $key = Storage::get($path);
            file_put_contents($cachedPath, $key);
            chmod($cachedPath, 0660);
        }

        $shouldCheckPermissions = config('app.debug') === false;

        return new CryptKey(
            'file://' . $cachedPath,
            null,
            $shouldCheckPermissions,
        );
    }
}
