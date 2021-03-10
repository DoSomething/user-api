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

        $progress = $this->output->createProgressBar($query->count());

        $query->chunkById(200, function (Collection $users) use ($progress) {
            $users->each(function (User $user) use ($progress) {
                // If the user is not subscribed to email:
                if (!$user->email_subscription_status) {
                    // Delete their profile entirely by muting promotions.
                    $user->promotions_muted_at = now();
                    $user->save();
                } else {
                    // Otherwise update their profile to remove the mobile number.
                    dispatch(new UpsertCustomerIoProfile($user))
                        ->onQueue(config('queue.names.low'));
                }

                $progress->advance();
            });
        });

        $this->info('Done!');
    }
}
