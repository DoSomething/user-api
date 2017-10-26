<?php

namespace Northstar\Services;
use GuzzleHttp\Client;
use App\Models\User;

class CustomerIo
{
    protected $client;

    public function __construct()
    {
        $url = config('services.customerio.url');
        $username = config('services.customerio.username');
        $password = config('services.customerio.password');

        $this->client = new Client([
            'base_uri' => $url,
            'defaults' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'auth' => [
                    'username' => $username,
                    'password' => $password,
                ],
            ],
        ]);
    }

    /**
     * Update a user in Customer.io
     *
     * @param  User   $user
     * @return bool   $success - Whether the update was a success.
     */
    public function updateProfile(User $user)
    {
        $response = $this->client->post('customers/' . $user->id, [
            'json': $user->toBlinkPayload(),
        ]);

        return $response->getStatusCode() === 200;
    }
}
