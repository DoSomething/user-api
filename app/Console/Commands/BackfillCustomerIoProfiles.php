<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use DoSomething\Gateway\Blink;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
use Northstar\Models\User;
use Northstar\Services\CustomerIo;

class BackfillCustomerIoProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:cio
                            {start : The date to begin back-filling records from.}
                            {end=now : The date to back-fill records until.}
                            {--created_at : Process records based on their created_at timestamp.}
                            {--throughput= : The maximum number of records to process per minute.}
                            {--bypass_blink : Bypass Blink and go straight to Customer.io}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send profiles updated after the given date to Customer.io';

    /**
     * The customer io service.
     *
     * @var CustomerIo
     */
    protected $customerIo;

    /**
     * Construct the command
     */
    public function __construct()
    {
        parent::__construct();

        $this->customerIo = new CustomerIo();
    }

    /**
     * Mark the given user as either updated or
     * log the failed id to the console.
     *
     * @param  User  $user
     * @param  boolean $isUpdated
     */
    private function markUserAsUpdated($user, $isUpdated)
    {
        if ($isUpdated) {
            $user->cio_backfilled = true;
            $user->save(['touch' => false]);

            $this->line('Successfully backfilled user '.$user->id);
        } else {
            $this->error('Failed to backfill user '.$user->id);
        }
    }

    /**
     * Backfill the given user using Blink.
     *
     * @param  User $user
     */
    private function backfillWithBlink($user) {
        try {
            gateway('blink')->userCreate($user->toBlinkPayload());

            $this->markUserAsUpdated($user, true);
        } catch (Exception $e) {
            $this->markUserAsUpdated($user, false);
        }
    }

    /**
     * Backfill the given user directly to Customer.Io
     *
     * @param  User $user
     */
    private function backfillWithCustomerIo($user) {
        $response = $this->customerIo->updateProfile($user);

        $this->markUserAsUpdated($user, $response);
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $start = new Carbon($this->argument('start'));
        $end = new Carbon($this->argument('end'));

        $byCreatedAt = $this->option('created_at');
        $throughput = $this->option('throughput');
        $bypassBlink = $this->option('bypass_blink');

        if ($byCreatedAt) {
            // If we pass `--created_at` flag, iterate over all users created in that time frame.
            $query = User::where(function (Builder $query) use ($start, $end) {
                $query->where('created_at', '>', $start)->where('created_at', '<', $end);
            });
        } else {
            // Iterate over users where the `mobile` field is not null (we skipped originally) or their
            // profile was updated in the given time frame, skipping ones we have already backfilled.
            $query = User::where(function (Builder $query) use ($start, $end) {
                $query->whereNotNull('mobile')->orWhere('updated_at', '>', $start);
            })->where('updated_at', '<', $end)->where('cio_backfilled', '!=', true);
        }

        $query->chunkById(200, function (Collection $users) use ($throughput, $bypassBlink) {
            // Send each of the loaded users to be processed.

            $users->each(function (User $user) use ($throughput, $bypassBlink) {
                if ($bypassBlink) {
                    $this->backfillWithCustomerIo($user);
                } else {
                    $this->backfillWithBlink($user);
                }

                // If the `--throughput #` parameter is set, make sure we can't
                // process more than # users per minute by taking a little nap.
                throttle($throughput);
            });
        });
    }
}
