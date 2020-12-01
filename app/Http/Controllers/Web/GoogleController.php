<?php

namespace App\Http\Controllers\Web;

use App\Auth\Registrar;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Google;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleController extends Controller
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Redirect unsuccessful authentication requests.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectUnsuccessfulRequest($message = null)
    {
        return redirect('/register')->with(
            'status',
            $message ?: 'Unable to verify Google account.',
        );
    }

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
            ->scopes([
                'profile',
                'https://www.googleapis.com/auth/user.birthday.read',
            ])
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
            $client = app('App\Services\Google');

            $googleProfile = $client->getProfile($googleUser->token);
        } catch (RequestException | ClientException | InvalidStateException $e) {
            logger()->warning('google_token_mismatch');

            return $this->redirectUnsuccessfulRequest();
        }

        $email = $googleUser->email;

        $northstarUser = $this->registrar->resolve(['email' => $email]);

        $firstName = data_get($googleUser->user, 'given_name');
        $lastName = data_get($googleUser->user, 'family_name');

        // If this is a new registration, ensure we've received the required profile fields.
        if (!$northstarUser && (!$firstName || !$lastName)) {
            return $this->redirectUnsuccessfulRequest(
                'We need your first and last name to create your account! Please confirm that these are set on your Google profile and try again.',
            );
        }

        $birthday = null;
        // If birthdate is not set on the google profile, we won't receive a 'birthdays' field.
        if (property_exists($googleProfile, 'birthdays')) {
            // Some date properties in this array may not contain a year property.
            $birthdaysWithYear = array_filter(
                $googleProfile->birthdays,
                function ($item) {
                    return isset($item->date->year);
                },
            );
            $birthday = data_get(Arr::first($birthdaysWithYear), 'date');
        }

        // Aggregate Google profile fields.
        $fields = [
            'google_id' => $googleUser->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
        ];

        if ($birthday) {
            $fields['birthdate'] = Carbon::createFromDate(
                $birthday->year,
                $birthday->month,
                $birthday->day,
            );
        }

        if ($northstarUser) {
            $northstarUser->updateIfNotSet($fields);
            $northstarUser->save();

            Auth::login($northstarUser, true);
            logger()->info('google_authentication');

            return redirect()->intended('/');
        } else {
            $fields['email'] = $email;

            $northstarUser = $this->registrar->registerViaWeb(
                $fields,
                'google',
            );

            Auth::login($northstarUser, true);
            logger()->info('google_authentication');

            return redirect('profile/about');
        }
    }
}
