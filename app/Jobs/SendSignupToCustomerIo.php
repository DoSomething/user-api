<?php

namespace App\Jobs;

use App\Jobs\Middleware\CustomerIoRateLimit;
use App\Models\Signup;
use App\Services\CustomerIo;

class SendSignupToCustomerIo extends Job
{
    /**
     * The signup to send to Customer.io.
     *
     * @var Signup
     */
    protected $signup;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Signup $signup)
    {
        $this->signup = $signup;
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new CustomerIoRateLimit()];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        $customerIo->trackEvent(
            $this->signup->user(),
            'campaign_signup',
            $this->signup->toCustomerIoPayload(),
        );
    }
}
