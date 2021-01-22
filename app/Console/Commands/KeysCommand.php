<?php

namespace App\Console\Commands;

use App\Auth\Repositories\KeyRepository;
use Illuminate\Console\Command;
use League\Flysystem\FileExistsException;
use phpseclib\Crypt\RSA;

class KeysCommand extends Command
{
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
        $keys = $rsa->createKey(4096);

        try {
            $repository->writePublicKey($keys['publickey']);
            $repository->writePrivateKey($keys['privatekey']);

            $this->info('Encryption keys generated successfully.');
        } catch (FileExistsException $exception) {
            $this->info('Keys already exist.');
        }
    }
}
