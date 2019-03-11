<?php

namespace Northstar\Http\Controllers\Web;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Contracts\Auth\Factory as Auth;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Laravel\Socialite\Two\InvalidStateException;
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
        // Grab the user's profile using their Facebook OAuth token.
        try {
            $requestUser = Socialite::driver('facebook')->user();
            $facebookUser = Socialite::driver('facebook')
                ->fields(['email', 'first_name', 'last_name', 'birthday'])
                ->userFromToken($requestUser->token);
        } catch (RequestException | ClientException | InvalidStateException $e) {
            logger()->warning('facebook_token_mismatch');

            return redirect('/register')->with('status', 'Unable to verify Facebook account.');
        }

        // If we were denied access to read email, do not log them in.
        if (empty($facebookUser->email)) {
            logger()->info('facebook_email_hidden');

            return redirect('/register')->with('status', 'We need your email to contact you if you win a scholarship.');
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

            $northstarUser = $this->registrar->register($fields, null, function (User $user) {
                $user->setSource(null, 'facebook');
            });
        }

        $this->auth->guard('web')->login($northstarUser, true);
        logger()->info('facebook_authentication');

        return redirect()->intended('/');
    }
}
