<?php

namespace Northstar\Services;

class Google
{
    /**
     * The Google API client.
     *
     * @var client
     */
    protected $client;

    /**
     * Create a new Google API client.
     */
    public function __construct()
    {
        // @see https://developers.google.com/people/api/rest/v1/people/get
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://people.googleapis.com/v1/',
        ]);
    }

    /**
     * Returns authenticated user profile.
     * @param string $token
     * @return object
     */
    public function getProfile($token)
    {
        $response = $this->client->get('people/me?personFields=birthdays', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
