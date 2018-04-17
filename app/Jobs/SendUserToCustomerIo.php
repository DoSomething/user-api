<?php

namespace Northstar\Jobs;

use Northstar\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendUserToCustomerIo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The serialized user model.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Rate limit Blink/Customer.io API requests to 10/s.
        $throttler = Redis::throttle('customerio')->allow(10)->every(1);
        $throttler->then(function () {
            // Send to Customer.io
            $shouldSendToCustomerIo = config('features.blink');
            if ($shouldSendToCustomerIo) {
                $blinkPayload = $this->user->toCustomerIoPayload();
                gateway('blink')->userCreate($blinkPayload);
            }

            // Log
            $verb = $shouldSendToCustomerIo ? 'sent' : 'would have been sent';
            info('User ' . $this->user->id . ' ' . $verb . ' to Customer.io');

            // KATIE - IS THIS TRUE????
            // @NOTE: Queue runner does not fire model events, so this will
            // not trigger another Blink/C.io call. See 'AppServiceProvider'.
            $this->user->cio_full_backfill = true;
            $this->user->save();
        }, function () {
            // Could not obtain lock... release to the queue.
            return $this->release(10);
        });
    }

    /**
     * Get the id of the user we are sending to Customer.io
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user->id;
    }
}
