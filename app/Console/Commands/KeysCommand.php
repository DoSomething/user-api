<?php

namespace App\Console\Commands;

use App\Auth\Repositories\KeyRepository;
use Defuse\Crypto\Key as Defuse;
use DFurnes\Environmentalist\ConfiguresApplication;
use Illuminate\Console\Command;
use League\Flysystem\FileExistsException;
use phpseclib\Crypt\RSA;

class KeysCommand extends Command
{
    use ConfiguresApplication;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:keys';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create public & private key for signing tokens.';

    /**
     * Execute the console command.
     *
     * @param RSA $rsa
     * @return void
     */
    public function handle(RSA $rsa, KeyRepository $repository)
    {
        // Create encryption key for refresh tokens, etc:
        $appAuthKey = Defuse::createNewRandomKey()->saveToAsciiSafeString();
        $this->writeEnvironmentVariable('APP_AUTH_KEY', $appAuthKey);
        $this->info('Saved new \'APP_AUTH_KEY\' environment variable.');

        // Create OAuth public/private RSA key pair:
        $keys = $rsa->createKey(4096);

        try {
            $repository->writePublicKey($keys['publickey']);
            $repository->writePrivateKey($keys['privatekey']);

            $this->info('Public/private key pair generated successfully.');
        } catch (FileExistsException $exception) {
            $this->info('Public/private key pair already exists.');
        }
    }
}
