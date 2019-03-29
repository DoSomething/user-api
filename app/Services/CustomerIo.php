<?php

namespace Northstar\Services;

class CustomerIo
{
    /**
     * The Customer.io client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Create a new Customer.io API client.
     */
    public function __construct()
    {
        $config = config('services.customerio');

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $config['url'],
            'auth' => [$config['username'], $config['password']],
        ]);
    }

    /**
     * Track Customer.io event for given user with given name and data.
     *
     * @param User $user
     * @param User $user
     */
    public function trackEvent($user, $payload)
    {
        return $this->client->post('customers/'.$user->id.'/events', [
            'form_params' => $payload,
        ]);
    }
}
