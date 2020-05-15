<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Northstar\Http\Controllers\Controller;
use Laravel\Socialite\Two\InvalidStateException;
use Northstar\Auth\Registrar;
use Northstar\Models\User;

class FacebookController extends Controller
{
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
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
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

        $email = $facebookUser->email;

        // If we were denied access to read email, do not log them in.
        if (empty($email)) {
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

        $northstarUser = $this->registrar->resolve(['email' => $email]);

        if ($northstarUser) {
            $northstarUser->updateIfNotSet($fields);
            $northstarUser->save();

            Auth::login($northstarUser, true);
            logger()->info('facebook_authentication');

            return redirect()->intended('/');
        } else {
            $fields['email'] = $email;

            $northstarUser = $this->registrar->registerViaWeb($fields, 'facebook');

            Auth::login($northstarUser, true);
            logger()->info('facebook_authentication');

            return redirect('profile/about');
        }
    }
}
