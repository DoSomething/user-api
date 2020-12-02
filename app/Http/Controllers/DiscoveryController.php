<?php

namespace App\Http\Controllers;

class DiscoveryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @see http://openid.net/specs/openid-connect-discovery-1_0.html
     * @return array
     */
    public function index()
    {
        // Grab the canonical app URL from config.
        $url = config('app.url');

        return [
            'issuer' => $url,
            'authorization_endpoint' => url($url . '/authorize'),
            'token_endpoint' => url($url . '/v2/auth/token'),
            'userinfo_endpoint' => url($url . '/v2/userinfo'),
            'jwks_uri' => url($url . '/v2/keys'),

            'response_types_supported' => ['code'],
            'subject_types_supported' => ['public'],
            'id_token_signing_alg_values_supported' => ['RS256'],
        ];
    }
}
