<?php

namespace App\Jobs;

use App\Services\CustomerIo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Redis;

class SendCustomerIoEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The email address to send a transactional email to.
     *
     * @var string
     */
    protected $to;

    /**
     * Create a new job instance.
     *
     * @param string $to
     * @return void
     */
    public function __construct($to)
    {
        $this->to = $to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        // Rate limit Customer.io API requests to 10/s.
        $throttler = Redis::throttle('customerio')
            ->allow(10)
            ->every(1);

        $throttler->then(
            function () use ($customerIo) {
                $customerIo->sendEmail($this->to);

                logger('Sent Customer.io transactional email', ['to' => $this->to]);
            },
            function () {
                // Could not obtain lock... release to the queue.
                return $this->release(10);
            },
        );
    }
}
