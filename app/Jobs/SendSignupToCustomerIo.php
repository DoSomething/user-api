<?php

namespace App\Jobs;

use App\Jobs\Middleware\CustomerIoRateLimit;
use App\Models\Signup;
use App\Services\CustomerIo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSignupToCustomerIo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
