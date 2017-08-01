<?php

namespace Northstar\Http\Controllers\Web;

use Socialite;
use Illuminate\Contracts\Auth\Factory as Auth;
use GuzzleHttp\Exception\RequestException;
use DoSomething\StatHat\Client as StatHat;
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
     * The StatHat client.
     *
     * @var StatHat
     */
    protected $stathat;

    /**
     * Make a new FacebookController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Auth $auth
     * @param Registrar $registrar
     * @param StatHat $stathat
     */
    public function __construct(Auth $auth, Registrar $registrar, StatHat $stathat)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;
        $this->stathat = $stathat;
    }

    /**
     * Redirect the user to the Facebook authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')
            ->scopes(['user_birthday'])
            ->redirect();
    }

    /**
     * Obtain the user information from Facebook.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $requestUser = Socialite::driver('facebook')->user();

        // Grab the user profile using their oauth token.
        try {
            $facebookUser = Socialite::driver('facebook')
                ->fields(['email', 'first_name', 'last_name', 'birthday'])
                ->userFromToken($requestUser->token);
        } catch (RequestException $e) {
            $this->stathat->ezCount('facebook token mismatch');
            return redirect('/register')->with('status', 'Unable to verify Facebook account.');
        }

        // If we were denied access to read email, do not log them in.
        if (empty($facebookUser->email)) {
            $this->stathat->ezCount('facebook email hidden');
            return redirect('/register')->with('status', 'Unable to verify Facebook account.');
        }

        // Aggregate public profile fields
        $fields = [
            'facebook_id' => $facebookUser->id,
            'first_name' => $facebookUser->user['first_name'],
            'last_name' => $facebookUser->user['last_name'],
        ];

        // Aggregate scoped fields
        if (isset($facebookUser->user['birthday'])) {
            $fields['birthdate'] = format_birthdate($facebookUser->user['birthday']);
        }

        $northstarUser = User::where('email', '=', $facebookUser->email)->first();

        if ($northstarUser) {
            $northstarUser->fillUnlessNull($fields);
            $northstarUser->save();
        } else {
            $fields['email'] = $facebookUser->email;
            $fields['country'] = country_code();
            $fields['language'] = app()->getLocale();

            $northstarUser = $this->registrar->register($fields, null);
        }

        $this->auth->guard('web')->login($northstarUser, true);
        $this->stathat->ezCount('facebook authentication');

        return redirect()->intended('/');
    }
}
