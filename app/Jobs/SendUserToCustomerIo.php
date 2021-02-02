<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\CustomerIo;
use Illuminate\Support\Facades\Redis;

class SendUserToCustomerIo extends Job
{
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
    public function handle(CustomerIo $customerIo)
    {
        // Rate limit Customer.io API requests to 10/s.
        $throttler = Redis::throttle('customerio')
            ->allow(10)
            ->every(1);
        $throttler->then(
            function () use ($customerIo) {
                $customerIo->updateCustomer($this->user);
            },
            function () {
                // Could not obtain lock... release to the queue.
                return $this->release(10);
            },
        );
    }
}
