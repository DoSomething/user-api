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
     * Run a GraphQL query using the client and return the data result.
     *
     * @param  $query     String
     * @param  $variables Array
     * @return array
     */
    public function query($query, $variables)
    {
        $response = $this->client->query($query, $variables);

        return $response ? $response->getData() : [];
    }

    /**
     * Query for a School by ID.
     *
     * @param  $schoolId String
     * @return array
     */
    public function getSchoolById($schoolId)
    {
        $query = '
        query GetSchoolById($schoolId: String!) {
          school(id: $schoolId) {
            name
            state
          }
        }';

        $variables = [
            'schoolId' => $schoolId,
        ];

        return $this->query($query, $variables)['school'];
    }
}
