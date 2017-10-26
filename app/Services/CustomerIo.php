<?php

namespace Northstar\Services;

use GuzzleHttp\Client;
use Northstar\Models\User;

class CustomerIo
{
    protected $client;

    public function __construct()
    {
        $url = config('services.customerio.url');

        $this->client = new Client([
            'base_uri' => $url,
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ]);
    }

    /**
     * Get the auth parameters required to make the request.
     * @return array
     */
    private function getAuthParams()
    {
        $username = config('services.customerio.username');
        $password = config('services.customerio.password');

        return [
            $username,
            $password,
        ];
    }

    /**
     * Update a user in Customer.io
     *
     * @param  User   $user
     * @return bool   $success - Whether the update was a success.
     */
    public function updateProfile(User $user)
    {
        // If the user doesn't have an email or phone number, don't send them.
        if (! $user->email || ! $user->phone) {
            return false;
        }

        $response = $this->client->put('customers/'.$user->id, [
            'json' => $user->toCustomerIoPayload(),
            'auth' => $this->getAuthParams(),
        ]);

        return $response->getStatusCode() === 200;
    }
}
