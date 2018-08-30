<?php

namespace Northstar\Console\Commands;

use Defuse\Crypto\Key;
use Illuminate\Console\Command;
use DFurnes\Environmentalist\ConfiguresApplication;

class SetupCommand extends Command
{
    use ConfiguresApplication;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:setup {--reset}';

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
        $this->writeEnvironmentVariable('APP_AUTH_KEY', $key->saveToAsciiSafeString());

        $this->runCommand('key:generate', 'Creating application key');

        $this->runCommand('northstar:keys', 'Creating public/private key');

        $this->runCommand('migrate', 'Running database migrations');
    }
}
