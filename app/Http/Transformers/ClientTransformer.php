<?php

namespace Northstar\Http\Transformers;

use Illuminate\Support\Str;
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
            'title' => ! empty($client->title) ? $client->title : Str::title($client->client_id),
            'description' => $client->description,

            'client_id' => $client->client_id,
            'client_secret' => $client->client_secret,
            'scope' => $client->scope,

            'allowed_grant' => $client->allowed_grant,
            'redirect_uri' => is_array($client->redirect_uri) ? $client->redirect_uri : [$client->redirect_uri],

            'refresh_tokens' => $client->getRefreshTokenCount(),

            'updated_at' => $client->updated_at->toISO8601String(),
            'created_at' => $client->created_at->toISO8601String(),
        ];
    }
}
