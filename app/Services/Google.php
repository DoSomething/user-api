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
     * @param string $token
     */
    public function __construct($token = '')
    {
        // @see https://developers.google.com/people/api/rest/v1/people/get
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => 'https://people.googleapis.com/v1/',
            'headers' =>  [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);
    }

    /**
     * Returns authenticated user profile.
     * @return object
     */
    public function getProfile()
    {
        $response = $this->client->get('people/me?personFields=birthdays');

        return json_decode($response->getBody()->getContents());
    }
}
