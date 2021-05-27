<?php

namespace App\Services;

class RockTheVote
{
    /**
     * The Rock The Vote API client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Create a new Rock The Vote API client.
     */
    public function __construct()
    {
        $config = config('services.rock_the_vote');

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $config['url'],
        ]);

        $this->authParams = [
            'partner_API_key' => $config['api_key'],
            'partner_id' => $config['partner_id'],
        ];
    }

    /**
     * Creates a Rock The Vote report.
     * @see https://rock-the-vote.github.io/Voter-Registration-Tool-API-Docs/#reports
     *
     * @param array $params
     * @return array
     */
    public function createReport($params)
    {
        $response = $this->client->post('/api/v4/registrant_reports', [
            'json' => array_merge($params, $this->authParams),
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a Rock The Vote report by ID.
     * @see https://rock-the-vote.github.io/Voter-Registration-Tool-API-Docs/#report_status
     *
     * @param int $id
     * @return array
     */
    public function getReportStatusById($id)
    {
        $response = $this->client->get('/api/v4/registrant_reports/' . $id, [
            'query' => $this->authParams,
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get a Rock The Vote report by URL.
     *
     * @param string $url
     * @return string
     */
    public function getReportByUrl($url)
    {
        $response = $this->client->get($url, [
            'query' => $this->authParams,
        ]);

        return $response->getBody();
    }
}
