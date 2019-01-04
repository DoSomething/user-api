<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Northstar\Jobs\SendUserToCustomerIo;

class BackfillCustomerIo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio';

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
    public function handle()
    {
        $query = (new User)->newQuery();
        $progress = $this->output->createProgressBar($query->count());

        $query->chunkById(200, function (Collection $users) use ($progress) {
            $users->each(function (User $user) use ($progress) {
                $queue = config('queue.names.low');

                dispatch(new SendUserToCustomerIo($user))->onQueue($queue);
                $progress->advance();
            });
        });

        $this->info(' Done!');
    }
}
