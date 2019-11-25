<?php

namespace Northstar\Services;

use Softonic\GraphQL\ClientBuilder;

class GraphQL
{
    /**
     * The GraphQL client.
     *
     * @var client
     */
    protected $client;

    /**
     * Create a new GraphQL client.
     */
    public function __construct()
    {
        $this->client = ClientBuilder::build(config('services.graphql.url'));
    }

    /**
     * Returns School information for given id.
     *
     * @param string $schoolId
     * @return object
     */
    public function getSchoolById($schoolId)
    {
        $response = $this->client->get('people/me?personFields=birthdays', [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody()->getContents());
    }
}
