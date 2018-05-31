<?php

namespace Northstar\Auth\Repositories;

use League\OAuth2\Server\CryptKey;

use Storage;

class KeyRepository
{
    public function hasKeys() {
        return Storage::has('keys/public.key') || Storage::has('keys/private.key');
    }

    public function getPublicKey()
    {
        return $this->getFileCached('keys/public.key');
    }

    public function writePublicKey($key) {
        Storage::write('keys/public.key', $key);
    }

    public function getPrivateKey()
    {
        return $this->getFileCached('keys/private.key');
    }

    public function writePrivateKey($key) {
        Storage::write('keys/private.key', $key);
    }

    protected function getFileCached($path)
    {
        $cachedPath = storage_path('cache/'. $path);

        // If this isn't cached locally, fetch it from storage driver.
        if (! file_exists($cachedPath)) {
            if (! file_exists(storage_path('cache/keys'))) {
                mkdir(storage_path('cache/keys'), 0777, true);
            }

            $key = Storage::get($path);
            file_put_contents($cachedPath, $key);
        }

        $shouldCheckPermissions = config('app.debug') === false;

        return new CryptKey('file://'.$cachedPath, null, $shouldCheckPermissions);
    }
}
