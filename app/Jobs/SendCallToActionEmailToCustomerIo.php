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
     * @see https://github.com/DoSomething/blink/wiki/Message-Schemas#calltoactionemailmessage
     *
     * @var array
     */
    protected $params;

    /**
     * Create a new job instance.
     *
     * @param array $params
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * Returns params for the Call To Action Email event.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
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
