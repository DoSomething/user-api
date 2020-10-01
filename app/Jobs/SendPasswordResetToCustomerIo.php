<?php

namespace Northstar\Jobs;

use Northstar\Models\User;
use Illuminate\Bus\Queueable;
use Northstar\Services\CustomerIo;
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
     * @var User
     */
    protected $user;

    /**
     * The password reset token.
     *
     * @var string
     */
    protected $token;

    /**
     * The password reset type.
     *
     * @var string
     */
    protected $type;

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
        $this->token = $token;
        $this->type = $type;
    }

    /**
     * Returns the password reset URL sent as the Call To Action Email actionUrl.
     *
     * @return array
     */
    public function getUrl()
    {
        return $this->user->getPasswordResetUrl($this->token, $this->type);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        // Rate limit Customer.io API requests to 10/s.
        $throttler = Redis::throttle('customerio')->allow(10)->every(1);
        $throttler->then(function () use ($customerIo) {
            $customerIo->trackEvent($this->user, 'call_to_action_email', [
                'actionUrl' => $this->getUrl(),
                'type' => $this->type,
                'userId' => $this->user->id,
            ]);
        }, function () {
            // Could not obtain lock... release to the queue.
            return $this->release(10);
        });
    }
}
