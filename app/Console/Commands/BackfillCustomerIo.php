<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Northstar\Services\CustomerIo;
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
    public function handle(CustomerIo $customerIo)
    {
        $query = User::where('cio_full_backfill', '!=', true);
        $progress = $this->output->createProgressBar($query->count());

        $query->chunkById(200, function (Collection $users) use ($customerIo, $progress) {
            $users->each(function (User $user) use ($customerIo, $progress) {
                dispatch(new SendUserToCustomerIo($user));
                $progress->advance();
            });
        });

        $this->info(' Done!');
    }
}
