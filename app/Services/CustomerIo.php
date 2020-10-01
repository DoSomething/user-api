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
     * Is Customer.io enabled for this app?
     *
     * @return bool
     */
    protected function enabled(): bool
    {
        return config('features.blink');
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
        $payload = ['name' => $eventName, 'data' => $eventData];

        if (! $this->enabled()) {
            info('Event "' . $eventName . '" would have been sent to Customer.io', ['id' => $user->id, 'payload' => $payload]);

            return;
        }

        $response = $this->client->post('customers/'.$user->id.'/events', [
            'json' => $payload,
        ]);

        // For this endpoint, any status besides 200 means something is wrong:
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Customer.io error: '.(string) $response->getBody());
        }

        info('Event "' . $eventName . '" sent to Customer.io', ['id' => $user->id]);
    }

    /**
     * Create or update the given customer's profile in Customer.io.
     * @see https://customer.io/docs/api/#apitrackcustomerscustomers_update
     *
     * @param User $user
     */
    public function updateCustomer(User $user)
    {
        $payload = $user->toCustomerIoPayload();

        if (! $this->enabled()) {
            info('User would have been sent to Customer.io', ['id' => $user->id, 'payload' => $payload]);

            return;
        }

        $response = $this->client->put('customers/'.$user->id, [
            'json' => $payload,
        ]);

        // For this endpoint, any status besides 200 means something is wrong:
        if ($response->getStatusCode() !== 200) {
            throw new Exception('Customer.io error: '.(string) $response->getBody());
        }

        info('User sent to Customer.io', ['id' => $user->id]);
    }

    /**
     * Delete the given user's profile in Customer.io
     * @see https://customer.io/docs/api/#apitrackcustomerscustomers_delete
     *
     * @param string $id
     */
    public function deleteUser(string $id)
    {
        if (! config('features.delete-api')) {
            info('User '.$id.' would have been deleted in Customer.io.');

            return;
        }

        return $this->client->delete('customers/'.$id);
    }
}
