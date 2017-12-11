<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Northstar\Services\CustomerIo;
use Illuminate\Support\Collection;

class BackfillCustomerIo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio {--throughput= : Maximum number of records to process per minute.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-export all profiles to Customer.io.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        $throughput = $this->option('throughput');

        $query = User::where('cio_full_backfill', '!=', true);
        $query->chunkById(200, function (Collection $users) use ($customerIo, $throughput) {
            $users->each(function (User $user) use ($customerIo, $throughput) {
                $success = $customerIo->updateProfile($user);
                if ($success) {
                    // @NOTE: See 'AppServiceProvider' for disabled model event.
                    $user->cio_full_backfill = true;
                    $user->save(['timestamps' => false]);

                    $this->line('Sent user '.$user->id.' to Customer.io');
                } else {
                    $this->error('Failed to backfill user '.$user->id);
                }

                // If the `--throughput #` parameter is set, make sure we can't
                // process more than # users per minute by taking a little nap.
                throttle($throughput);
            });
        });
    }
}
