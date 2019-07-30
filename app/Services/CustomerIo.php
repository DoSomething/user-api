<?php

namespace Northstar\Services;

use Northstar\Models\User;

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
     * @see https://customer.io/docs/api/#apitrackeventsevent_add
     *
     * @param User $user
     * @param string $eventName
     * @param array $eventData
     */
    public function trackEvent($user, $eventName, $eventData = [])
    {
        $payload = ['name' => $eventName];

        foreach ($eventData as $key => $value) {
            $payload["data[$key]"] = $value;
        }

        return $this->client->post('customers/'.$user->id.'/events', [
            'form_params' => $payload,
        ]);
    }

    /**
     * Delete the given user's profile in Customer.io
     * @see https://customer.io/docs/api/#apitrackcustomerscustomers_delete
     *
     * @param User $user
     */
    public function deleteUser(User $user)
    {
        return $this->client->delete('customers/'.$user->id);
    }
}
