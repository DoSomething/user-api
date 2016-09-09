<?php

namespace Northstar\Services;

use GuzzleHttp\Client;

class Facebook
{
    /**
     * HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Client ID for the DoSomething Facebook App.
     *
     * @var string
     */
    protected $client_id;

    /**
     * Client secret for the DoSomething Facebook App.
     *
     * @var string
     */
    protected $client_secret;

    public function __construct()
    {
        $this->client_secret = config('services.facebook.client_secret');
        $this->client_id = config('services.facebook.client_id');

        $this->client = new Client([
            'base_uri' => config('services.facebook.url'),
        ]);
    }

    /**
     * Checks if the given Facebook token is valid & works for the given FB user
     * @param  string $input_token Token to check
     * @param  string $facebook_id Facebook ID to compare against
     * @return bool
     */
    public function verifyToken($input_token, $facebook_id)
    {
        $response = $this->client->request('GET', 'debug_token', [
            'query' => ['access_token' => $this->client_id.'|'.$this->client_secret, 'input_token' => $input_token],
        ]);

        $verification = json_decode($response->getBody()->getContents(), true)['data'];

        return $verification['is_valid'] && $verification['user_id'] == $facebook_id;
    }
}
