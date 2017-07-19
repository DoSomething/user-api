<?php

namespace Northstar\Http\Controllers\Web;

use Socialite;
use Illuminate\Contracts\Auth\Factory as Auth;
use GuzzleHttp\Exception\RequestException;
use Northstar\Auth\Registrar;
use Northstar\Models\User;

class FacebookController extends Controller
{
    /**
     * The authentication factory.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new FacebookController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Auth $auth
     * @param Registrar $registrar
     * @param AuthorizationServer $oauth
     */
    public function __construct(Auth $auth, Registrar $registrar)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $requestUser = Socialite::driver('facebook')->user();

        try {
            // Confirm the details are real by asking for them again with the given Facebook token.
            // This token only works if the user is asking for their profile information.
            $facebookUser = Socialite::driver('facebook')->userFromToken($requestUser->token);
        } catch (RequestException $e) {
            return redirect('/login')->with('status', 'Unable to verify Facebook account.');
        }

        $northstarUser = User::where('email', '=', $facebookUser->email)->first();
        $name = get_first_and_last($facebookUser->name);

        if (! $northstarUser) {
            $fields = [
                'email' => $facebookUser->email,
                'facebook_id' => $facebookUser->id,
                'first_name' => $name['first_name'],
                'last_name' => $name['last_name'],
                'country' => country_code(),
                'language' => app()->getLocale(),
            ];

            $northstarUser = $this->registrar->register($fields, null);
        } else {
            // Fill in any blank fields with Facebook values.
            $northstarUser->facebook_id ? null : $northstarUser->facebook_id = $facebookUser->id;
            $northstarUser->first_name ? null : $northstarUser->fist_name = $name['first_name'];
            $northstarUser->last_name ? null : $northstarUser->last_name = $name['last_name'];
            $northstarUser->save();
        }

        $this->auth->guard('web')->login($northstarUser, true);

        // TODO: Implement a feature flag for Phoenix requests, then uncomment this email request.
        // $this->registrar->sendWelcomeEmail($northstarUser);

        return redirect()->intended('/');
    }
}
