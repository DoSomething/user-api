<?php

namespace App\Console\Commands;

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
        $query = User::whereNull('promotions_muted_at')
            ->whereNotNull('mobile')
            ->whereIn('sms_status', [null, 'stop', 'undeliverable']);

        $query->chunkById(200, function (Collection $users) {
            $users->each(function (User $user) {
                $this->info('Checking user ' . $user->id);

                // If the user is not subscribed to email:
                if (!$user->email_subscription_status) {
                    // Delete their profile entirely by muting promotions.
                    $user->mutePromotions();

                    return;
                }

                // Otherwise update their profile to remove the mobile number.
                dispatch(new UpsertCustomerIoProfile($user))->onQueue(
                    config('queue.names.low'),
                );
            });
        });

        $this->info('Done!');
    }
}
