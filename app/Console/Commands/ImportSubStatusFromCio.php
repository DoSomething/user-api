<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ImportSubStatusFromCio extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:importsub';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grab email subscription status from Customer.io for users on which it is not already set.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->line('Starting up...');

        // Create a Guzzle Client to use with the Customer.io Beta API
        $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://beta-api.customer.io',
        ]);

        // Customer.io authentication
        $auth = [config('services.customerio.username'), config('services.customerio.password')];

        $query = (new User)->newQuery();
        $query = $query->where('email', '!=', null);

        $query->chunkById(200, function (Collection $users) use ($client, $auth) {
            $users->each(function (User $user) use ($client, $auth) {
                // Make request to c.io to get that user's subscription status
                $response = $client->get('/v1/api/customers/' . $user->id . '/attributes', ['auth' => $auth]);
                $body = json_decode($response->getBody());
                $unsubscribed = $body->customer->unsubscribed;

                // Update subscription status on user
                $user->email_frequency = !$unsubscribed;
                $user->save();

                $this->line('Updated user: '.$user->id);

                // Make sure we don't go over 10 requests per second
                sleep(.1);

            });
        });

        $this->line('Done!');
    }
}
