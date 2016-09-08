<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class FacebookController extends Controller
{

    protected $client;

    public function __construct()
    {
        $base_url = config('services.facebook.url');
        $this->client_secret = config('services.facebook.client_secret');
        $this->client_id = config('services.facebook.client_id');

        $this->client = new Client([
            'base_uri' => $base_url
        ]);
    }

    public function validateToken(Request $request) {
        $this->validate($request, [
            'input_token' => 'required',
            'facebook_id' => 'required',
        ]);

        $response = $this->client->request('GET', 'debug_token', [
            'query' => ['access_token' => $this->client_id . '|' . $this->client_secret, 'input_token' => $request->input('input_token')],
        ]);

        $verification = json_decode($response->getBody()->getContents())->data;

        if ($verification->is_valid && $verification->user_id == $request->input('facebook_id')) {
            return $this->respond("Verified", 200);
        }

        return $this->respond("Invalid", 401);
    }
}
