<?php

namespace Northstar\Http\Controllers\Web;

use Socialite;
use Illuminate\Contracts\Auth\Factory as Auth;
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
        $facebookUser = Socialite::driver('facebook')->user();
        $northstarUser = User::where('email', '=', $facebookUser->email)->first();

        if (! isset($northstarUser)) {
            $fields = [
                'email' => $facebookUser->email,
                'facebook_id' => $facebookUser->id,
                'first_name' => $facebookUser->name, // QUESTION: Should we attempt to split the string here or leave it?
                'country' => country_code(),
                'language' => app()->getLocale(),
            ];

            $northstarUser = $this->registrar->register($fields, null);
        } else {
            // TODO: Sync the existing user with Facebook fields.
        }


        $this->auth->guard('web')->login($northstarUser, true);

        $this->registrar->sendWelcomeEmail($northstarUser);

        return redirect()->intended('/');
    }
}
