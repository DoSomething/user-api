<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;
use Illuminate\Support\Facades\Redis;

class SendPasswordUpdatedToCustomerIo extends Job
{
    /**
     * The serialized user model.
     *
     * @var User
     */
    protected $user;

    /**
     * The source of the password update.
     *
     * @var string
     */
    protected $updatedVia;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param string $updatedVia
     * @return void
     */
    public function __construct(User $user, $updatedVia)
    {
        $this->user = $user;
        $this->updatedVia = $updatedVia;
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
                $response = $customerIo->trackEvent(
                    $this->user,
                    'password_updated',
                    [
                        'updated_via' => $this->updatedVia,
                    ],
                );
                info(
                    "Sent password_updated for {$this->user->id} to Customer.io",
                    ['response' => $response],
                );
            },
            function () {
                // Could not obtain lock... release to the queue.
                return $this->release(10);
            },
        );
    }
}
