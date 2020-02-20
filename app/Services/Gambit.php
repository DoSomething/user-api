<?php

namespace Northstar\Services;

use Northstar\Models\User;

class Gambit
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
        $config = config('services.gambit');

        $this->client = new \GuzzleHttp\Client([
            'base_uri' => $config['url'],
            'auth' => [$config['username'], $config['password']],
        ]);
    }

    /**
     * Delete the given user's activity in Gambit.
     *
     * @see https://git.io/JvWb6
     *
     * @param string $id
     */
    public function deleteUser(string $id)
    {
        if (! config('features.delete-api')) {
            info('User '.$id.' would have been deleted in Gambit.');

            return;
        }

        return $this->client->delete('/api/v2/users/'.$id);
    }
}
