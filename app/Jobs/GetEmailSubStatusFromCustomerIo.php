<?php

namespace Northstar\Jobs;

use Redis;
use Northstar\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetEmailSubStatusFromCustomerIo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $key = 'GetEmailSubStatusFromCustomerIo-'.$this->user->id;

        Redis::throttle($key)->allow(10)->every(1)->then(function () {
            // Customer.io authentication
            $auth = [config('services.customerio.username'), config('services.customerio.password')];

            // Create a Guzzle Client to use with the Customer.io Beta API
            $client = new \GuzzleHttp\Client([
                'base_uri' => 'https://beta-api.customer.io',
                'auth' => $auth,
            ]);

            // Make request to c.io to get that user's subscription status
            $response = $client->get('/v1/api/customers/'.$this->user->id.'/attributes');
            $body = json_decode($response->getBody());
            $unsubscribed = $body->customer->unsubscribed;

            // Update subscription status on user
            $this->user->email_frequency = $unsubscribed ? 'none' : 'active';
            $this->user->save();

            info('User '.$this->user->id.' email subscription status grabbed from Customer.io');
        }, function () {
            info('Unable to get email subscription status for '.$this->user->id.' at this time, job pushed back onto queue.');

            return $this->release(10);
        });
    }
}
