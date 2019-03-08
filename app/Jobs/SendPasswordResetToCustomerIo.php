<?php

namespace Northstar\Jobs;

use Northstar\Models\User;
use Northstar\PasswordResetType;
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
     * The serialized user model.
     *
     * @var array
     */
    protected $params;
    /**
     * The serialized user model.
     *
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $token
     * @param string $type
     * @return void
     */
    public function __construct(User $user, $token, $type)
    {
        $this->user = $user;
        $this->params = PasswordResetType::getEmailVars($type);
        $this->params['userId'] = $this->user->id;
        $this->params['actionUrl'] = $this->user->getPasswordResetUrl($token, $type);
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
