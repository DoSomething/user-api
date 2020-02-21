<?php

namespace Northstar\Services;

use Northstar\Models\User;

class Rogue
{
    /**
     * The HTTP client.
     *
     * @var Client
     */
    protected $client;

    /**
     * Create a new Gambit API client.
     */
    public function __construct()
    {
        $config = config('services.rogue');

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $config['url'],
            'headers' => [
                'Authorization' => machine_token('activity', 'write', 'admin'),
            ],
        ]);
    }

    /**
     * Delete the given user's activity in Rogue.
     *
     * @see https://git.io/JvWb6
     *
     * @param string $id
     */
    public function deleteUser(string $id)
    {
        if (! config('features.delete-api')) {
            info('User '.$id.' would have been deleted in Rogue.');

            return;
        }

        return $this->client->delete('/api/v3/users/'.$id);
    }
}
