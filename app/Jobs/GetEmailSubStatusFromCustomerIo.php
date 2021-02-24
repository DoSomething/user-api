<?php

namespace App\Jobs;

use App\Models\User;
use Redis;

class GetEmailSubStatusFromCustomerIo extends Job
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
    public function handle()
    {
        /*
         * Rate limit to 10 requests/second for Beta API requests.
         * @see https://customer.io/docs/api/#tag/betaLimit
         */
        Redis::throttle('customerioemailsubstatus')
            ->allow(10)
            ->every(1)
            ->then(
                function () {
                    // Customer.io authentication
                    $auth = [
                        config('services.customerio.username'),
                        config('services.customerio.password'),
                    ];

                    // Create a Guzzle Client to use with the Customer.io Beta API
                    $client = new \GuzzleHttp\Client([
                        'base_uri' => 'https://beta-api.customer.io',
                        'auth' => $auth,
                    ]);

                    // Make request to c.io to get that user's subscription status
                    $response = $client->get(
                        '/v1/api/customers/' . $this->user->id . '/attributes',
                    );
                    $body = json_decode($response->getBody());
                    $unsubscribed = $body->customer->unsubscribed;
                    info(
                        '[GetEmailSubStatusFromCustomerIo] For user ' .
                            $this->user->id .
                            ' got unsubscribed=' .
                            $unsubscribed,
                    );

                    // Update subscription status on user
                    $this->user->email_subscription_status = $unsubscribed
                        ? false
                        : true;
                    $this->user->save();
                    info(
                        '[GetEmailSubStatusFromCustomerIo] For user ' .
                            $this->user->id .
                            ' set email_subscription_status=' .
                            $this->user->email_subscription_status,
                    );
                },
                function () {
                    info(
                        'Unable to get email subscription status for ' .
                            $this->user->id .
                            ' at this time, job pushed back onto queue.',
                    );

                    return $this->release(10);
                },
            );
    }
}
