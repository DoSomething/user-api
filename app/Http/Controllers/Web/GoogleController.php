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
use Northstar\Services\Google;

class GoogleController extends Controller
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new GoogleController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;
    }

    /**
     * Redirect the user to the Google authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('google')
            ->scopes(['profile', 'https://www.googleapis.com/auth/user.birthday.read'])
            ->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        // Fetch user's birthday using their Google OAuth token.
        try {
            $googleUser = Socialite::driver('google')->user();

            // @see https://developers.google.com/people/api/rest/v1/people/get
            $client = new Google($googleUser->token);

            $data = $client->getProfile();

            // TODO: Loop through $data->birthdays array, checking if each object entry has a data property that contains year. Use that as birthday.

            info(print_r($data->birthdays, true));
        } catch (RequestException | ClientException | InvalidStateException $e) {
            logger()->warning('google_token_mismatch');

            return redirect('/register')->with('status', 'Unable to verify Google account.');
        }

        // If we were denied access to read email, do not log them in.
        if (empty($googleUser->email)) {
            logger()->info('google_email_hidden');

            return redirect('/register')->with('status', 'We need your email to contact you if you win a scholarship.');
        }

        // Aggregate public profile fields
        $fields = [
            'google_id' => $googleUser->id,
            'first_name' => $googleUser->user['given_name'],
            'last_name' => $googleUser->user['family_name'],
        ];

        $northstarUser = $this->registrar->resolve(['email' => $googleUser->email]);

        if ($northstarUser) {
            $northstarUser->fillUnlessNull($fields);
            $northstarUser->save();
        } else {
            $fields['email'] = $googleUser->email;
            $fields['country'] = country_code();
            $fields['language'] = app()->getLocale();

            // Add same email settings as traditional new members
            $fields['email_subscription_status'] = true;
            $fields['email_subscription_topics'] = ['community'];

            $northstarUser = $this->registrar->register($fields, null, function (User $user) {
                $user->setSource(null, 'google');
            });
        }

        Auth::login($northstarUser, true);
        logger()->info('google_authentication');

        return redirect()->intended('/');
    }
}
