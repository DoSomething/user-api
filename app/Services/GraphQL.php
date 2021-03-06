<?php

namespace App\Services;

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
        $this->client = ClientBuilder::build(config('services.graphql.url'), [
            'headers' => ['apollographql-client-name' => 'northstar'],
        ]);
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
        // If we've disabled server-side GraphQL requests, return an optional
        // wrapper object (so reading keys from the query won't fail):
        if (config('features.graphql_php') === false) {
            return optional();
        }

        $response = $this->client->query($query, $variables);

        return $response ? $response->getData() : [];
    }

    /**
     * Query for a Club by ID.
     *
     * @param  $clubId Int
     * @return array
     */
    public function getClubById($clubId)
    {
        $query = '
        query GetClubQuery($clubId: Int!) {
          club(id: $clubId) {
            name
            leaderId
          }
        }';

        $variables = [
            'clubId' => $clubId,
        ];

        return $this->query($query, $variables)['club'];
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
        query GetSchoolQuery($schoolId: String!) {
          school(id: $schoolId) {
            name
            location
          }
        }';

        $variables = [
            'schoolId' => $schoolId,
        ];

        return $this->query($query, $variables)['school'];
    }

    /**
     * Query for a CampaignWebsite by campaignId field.
     *
     * NOTE: We prefer to bundle queries with their associated usage logic.
     * We've opted to utilize this helper method as an exception to avoid duplication.
     * (See thread https://git.io/fjoqd).
     *
     * @param  $campaignId String
     * @return array
     */
    public function getCampaignWebsiteByCampaignId($campaignId)
    {
        $query = '
        query GetCampaignWebsiteByCampaignId($campaignId: String!) {
          campaignWebsiteByCampaignId(campaignId: $campaignId) {
            title
            slug
          }
        }';

        $variables = [
            'campaignId' => $campaignId,
        ];

        return $this->query($query, $variables)['campaignWebsiteByCampaignId'];
    }
}
