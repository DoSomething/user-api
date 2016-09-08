<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class FacebookController extends Controller
{
    /**
     * HTTP client.
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * Client ID for the DoSomething Facebook App.
     *
     * @var string
     */
    protected $client_id;

    /**
     * Client secret for the DoSomething Facebook App.
     *
     * @var string
     */
    protected $client_secret;

    public function __construct()
    {
        $this->client_secret = config('services.facebook.client_secret');
        $this->client_id = config('services.facebook.client_id');

        $this->client = new Client([
            'base_uri' => config('services.facebook.url'),
        ]);
    }

    /**
     * Verifies if a given Facebook token is valid & corresponds to the Facebook ID
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return \Illuminate\Http\Response
     */
    public function validateToken(Request $request)
    {
        $this->validate($request, [
            'input_token' => 'required',
            'facebook_id' => 'required',
        ]);

        $response = $this->client->request('GET', 'debug_token', [
            'query' => ['access_token' => $this->client_id.'|'.$this->client_secret, 'input_token' => $request->input('input_token')],
        ]);

        $verification = json_decode($response->getBody()->getContents(), true)['data'];

        if ($verification['is_valid'] && $verification['user_id'] == $request->input('facebook_id')) {
            return $this->respond('Verified', 200);
        }

        return $this->respond('Invalid', 401);
    }
}
