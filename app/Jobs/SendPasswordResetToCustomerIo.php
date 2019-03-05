<?php

namespace Northstar\Jobs;

use Northstar\Mail\PasswordReset;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendPasswordResetToCustomerIo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The serialized PasswordReset mail.
     *
     * @var PasswordReset
     */
    protected $passwordReset;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(PasswordReset $passwordReset)
    {
        $this->passwordReset = $passwordReset;
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
                gateway('blink')->userPasswordReset($this->passwordReset->toCustomerIoPayload());
            }

            // Log
            $verb = $shouldSendToCustomerIo ? 'sent' : 'would have been sent';
            info('Password reset for '.$this->passwordReset->user->id.' '.$verb.' to Customer.io');
        }, function () {
            // Could not obtain lock... release to the queue.
            return $this->release(10);
        });
    }
}
