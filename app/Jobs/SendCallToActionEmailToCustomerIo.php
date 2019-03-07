<?php

namespace Northstar\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendCallToActionEmailToCustomerIo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The serialized Call To Action Email event parameters.
     *
     * @var array
     */
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Returns the action URL of the Call To Action Email.
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->params['actionUrl'];
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
            $shouldSendToCustomerIo = config('features.blink');
            if ($shouldSendToCustomerIo) {
                gateway('blink')->userCallToActionEmail($this->params);
            }

            $verb = $shouldSendToCustomerIo ? 'sent' : 'would have been sent';
            info('Call To Action Email for '.$this->params['userId'].' '.$verb.' to Customer.io');
        }, function () {
            // Could not obtain lock... release to the queue.
            return $this->release(10);
        });
    }
}
