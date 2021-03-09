<?php

namespace App\Console\Commands;

use App\Jobs\DeleteCustomerIoProfile;
use App\Jobs\UpsertCustomerIoProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RemoveCustomerIoMobile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio-remove-mobile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes mobile from all unsubscribed profiles in Customer.io.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Find all SMS unsubscribed users where promotions have not been muted.
        $query = (new User())->newQuery()
            ->whereNull('promotions_muted_at')
            ->whereIn('sms_status', ['stop', 'undeliverable']);

        $progress = $this->output->createProgressBar($query->count());

        $query->chunkById(200, function (Collection $users) use ($progress) {
            $users->each(function (User $user) use ($progress) {
                // If the user is subscribed to email:
                $job = $user->email_subscription_status ?
                    // Execute an update to remove mobile from their profile.
                    new UpsertCustomerIoProfile($user) :
                    // Otherwise delete the profile entirely.
                    new DeleteCustomerIoProfile($user);

                dispatch($job)->onQueue(config('queue.names.low'));
                $progress->advance();
            });
        });

        $this->info('Done!');
    }
}
