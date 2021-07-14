<?php

namespace App\Console\Commands;

use App\Auth\Registrar;
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
     * @return bool
     */
    public function handle(Registrar $registrar)
    {
        $username = $this->argument('username');
        $user = $registrar->resolve(['username' => $username]);

        if (!$user) {
            $this->error('User not found.');

            return 0;
        }

        if (!$user->totp) {
            $this->info('User does not have a TOTP device.');

            return 0;
        }

        if (
            $this->confirm(
                'Remove TOTP device from ' .
                    $username .
                    ' (' .
                    $user->id .
                    ')?',
            )
        ) {
            $user->totp = null;
            $user->save();

            $this->info('Removed TOTP device.');

            return 0;
        }
    }
}
