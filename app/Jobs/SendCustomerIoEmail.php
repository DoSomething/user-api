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
     * The Customer.io transactional message ID to send.
     *
     * @var int
     */
    protected $transactionalMessageId;

    /**
     * Key/value pairs to use within the transactional email content.
     *
     * @var array
     */
    protected $messageData;

    /**
     * Create a new job instance.
     *
     * @param string $to
     * @param int $transactionalMessageId
     * @param array $messageData
     * @return void
     */
    public function __construct($to, $transactionalMessageId, $messageData)
    {
        $this->to = $to;
        $this->transactionalMessageId = $transactionalMessageId;
        $this->messageData = $messageData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        /**
         * Rate limit Customer.io API requests to 100/s.
         * @see https://customer.io/docs/api/#tag/appLimit
         */
        $throttler = Redis::throttle('customerio')
            ->allow(100)
            ->every(1);

        $throttler->then(
            function () use ($customerIo) {
                $customerIo->sendEmail(
                    $this->to,
                    $this->transactionalMessageId,
                    $this->messageData,
                );
            },
            function () {
                // Could not obtain lock... release to the queue.
                return $this->release(10);
            },
        );
    }
}
