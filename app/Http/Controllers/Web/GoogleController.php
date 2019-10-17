<?php

namespace Northstar\Http\Controllers\Web;

use Carbon\Carbon;
use Illuminate\Support\Arr;
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
            // Use the service container so we can mock Google API requests in tests.
            // @see https://laravel.com/docs/5.5/helpers#method-app
            $client = app('Northstar\Services\Google');

            $googleProfile = $client->getProfile($googleUser->token);
        } catch (RequestException | ClientException | InvalidStateException $e) {
            logger()->warning('google_token_mismatch');

            return redirect('/register')->with('status', 'Unable to verify Google account.');
        }

        $email = $googleUser->email;
        // Some date properties in this array may not contain a year property.
        $birthdaysWithYear = array_filter($googleProfile->birthdays, function ($item) {
            return isset($item->date->year);
        });
        $birthday = Arr::first($birthdaysWithYear)->date;

        // Aggregate Google profile fields.
        $fields = [
            'google_id' => $googleUser->id,
            'first_name' => $googleUser->user['given_name'],
            'last_name' => $googleUser->user['family_name'],
            'birthdate' => Carbon::createFromDate($birthday->year, $birthday->month, $birthday->day),
        ];

        $northstarUser = $this->registrar->resolve(['email' => $email]);

        if ($northstarUser) {
            $northstarUser->updateIfNotSet($fields);
            $northstarUser->save();
        } else {
            $fields['email'] = $email;
            $fields = array_merge($fields, get_default_web_registration_fields());

            $northstarUser = $this->registrar->register($fields, null, function (User $user) {
                $user->setSource(null, 'google');
            });
        }

        Auth::login($northstarUser, true);
        logger()->info('google_authentication');

        return redirect()->intended('/');
    }
}
