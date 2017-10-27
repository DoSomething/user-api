<?php

namespace Northstar\Console\Commands;

use Illuminate\Support\Collection;
use Illuminate\Console\Command;
use Northstar\Models\User;

class RevertCustomerIoImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio-revert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revert all users marked as backfilled.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Iterate over all users we have backfilled.
        $query = User::where('cio_backfilled', '=', true);

        $query->chunkById(200, function (Collection $users) {
            // Send each of the loaded users to be processed.

            $users->each(function (User $user) {
                $user->cio_backfilled = false;
                $user->save(['touch' => false]);

                $this->line('Successfully reverted backfill mark for user '.$user->id);
            });
        });
    }
}
