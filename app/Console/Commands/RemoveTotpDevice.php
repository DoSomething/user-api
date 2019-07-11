<?php

namespace Northstar\Console\Commands;

use Northstar\Auth\Registrar;
use Illuminate\Console\Command;

class RemoveTotpDevice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'totp:remove {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove TOTP device from the given user.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(Registrar $registrar)
    {
        $username = $this->argument('username');
        $user = $registrar->resolve(['username' => $username]);

        if (! $user) {
            return $this->error('User not found.');
        }

        if (! $user->totp) {
            return $this->info('User does not have a TOTP device.');
        }

        if ($this->confirm('Remove TOTP device from '.$username.' ('.$user->id.')?')) {
            $user->totp = null;
            $user->save();

            return $this->info('Removed TOTP device.');
        }
    }
}
