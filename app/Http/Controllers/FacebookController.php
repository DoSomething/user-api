<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Northstar\Services\Facebook;

class FacebookController extends Controller
{
    /**
     * Facebook API wrapper.
     * @var Facebook
     */
    protected $facebook;

    public function __construct(Facebook $facebook)
    {
        $this->facebook = $facebook;

        $this->middleware('scope:admin');
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

        $verified = $this->facebook->verifyToken($request->input('input_token'), $request->input('facebook_id'));

        if ($verified) {
            return $this->respond('Verified', 200);
        }
        else {
            return $this->respond('Invalid', 401);
        }
    }
}
