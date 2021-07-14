<?php

namespace App\Console\Commands;

use Defuse\Crypto\Key;
use DFurnes\Environmentalist\ConfiguresApplication;
use Illuminate\Console\Command;

class SetupCommand extends Command
{
    use ConfiguresApplication;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:setup {--reset : Delete existing env file and recreate it.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure your application.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createEnvironmentFile($this->option('reset'));

        // Generate key and save to APP_AUTH_KEY in .env file
        $key = Key::createNewRandomKey();
        $this->writeEnvironmentVariable(
            'APP_AUTH_KEY',
            $key->saveToAsciiSafeString(),
        );

        $this->runArtisanCommand('key:generate', 'Creating application key');

        $this->runArtisanCommand(
            'northstar:keys',
            'Creating public/private key',
        );

        $this->runArtisanCommand('migrate:all', 'Running database migrations');
    }
}
