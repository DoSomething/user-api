<?php

namespace Northstar\Auth;

use Illuminate\Support\Str;
use Northstar\Gateway\GatewayTokenGuard;
use Illuminate\Contracts\Auth\Guard;
use Northstar\Models\Client;
use Northstar\Models\Token;
use Northstar\Models\User;

class NorthstarTokenGuard extends GatewayTokenGuard implements Guard
{
    /**
     * Get the token for the current request.
     *
     * @return void
     */
    protected function parseToken()
    {
        $header = $this->request->getHeader('authorization');
        $legacyKey = $this->request->getHeader('x-ds-rest-api-key');

        // If the provided token is 32 characters long, it's a legacy
        // database token (used by the mobile app).
        $isLegacyApiKey = ! empty($legacyKey);
        if ($isLegacyApiKey) {
            $client = Client::where('client_secret', $legacyKey[0])->first();
            $this->client = $client ? $client->client_id : null;
            $this->scopes = $client ? $client->scope : null;

            $isLegacyToken = ! empty($header) && Str::length($header[0]) === 32;
            if ($isLegacyToken) {
                $token = Token::where('key', $header[0])->first();
                $this->token = null;

                $this->user = User::find($token->user_id);
                $this->role = $this->user->role;
            }
        } else {
            parent::parseToken();
        }
    }
}
