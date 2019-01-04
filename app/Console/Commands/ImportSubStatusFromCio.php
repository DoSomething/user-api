<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Northstar\Jobs\GetEmailSubStatusFromCustomerIo;

class ImportSubStatusFromCio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:importsub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For each user, kick off a job to grab email subscription status from Customer.io.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Grab users who have email addresses
        $query = (new User)->newQuery();
        $query = $query->where('email', 'exists', true);

        $query->chunkById(200, function (Collection $users){
            $users->each(function (User $user){
                $queue = config('queue.names.low');

                dispatch(new GetEmailSubStatusFromCustomerIo($user))->onQueue($queue);
            });
        });

        $this->info('Queued up a job to grab email status for each user!');
    }
}
