<?php

namespace App\Services;

use App\Models\User;
use RuntimeException;

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
            'auth' => [$config['user'], $config['password']],
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
        if (!config('features.delete-api')) {
            info('User ' . $id . ' would have been deleted in Gambit.');

            return;
        }

        // We don't want to throw on 404 errors, since that's expected if a user has no activity:
        $response = $this->client->delete('/api/v2/users/' . $id, [
            'http_errors' => false,
        ]);
        $status = $response->getStatusCode();

        if ($status === 200) {
            info('User ' . $id . ' successfully deleted from Gambit.');

            return;
        }

        if ($status === 404) {
            info(
                'User ' .
                    $id .
                    ' did not have any conversation history in Gambit.',
            );

            return;
        }

        // Otherwise, something must have gone wrong...
        $json = $response->getBody()->getContents();
        $message = data_get($json, 'message', 'Unknown Error');
        throw new RuntimeException('Gambit Error: ' . $message);
    }
}
