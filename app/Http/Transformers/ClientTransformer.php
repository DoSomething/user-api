<?php

namespace Northstar\Http\Transformers;

use Northstar\Models\Client;
use League\Fractal\TransformerAbstract;

class ClientTransformer extends TransformerAbstract
{
    /**
     * @param Client $client
     * @return array
     */
    public function transform(Client $client)
    {
        return [
            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => $client->scope,

            'updated_at' => $client->updated_at->toISO8601String(),
            'created_at' => $client->created_at->toISO8601String(),

            // DEPRECATED:
            'app_id' => $client->app_id,
            'api_key' => $client->api_key,
        ];
    }
}
