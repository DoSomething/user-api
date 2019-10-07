<?php

namespace Northstar\Http\Controllers\Legacy;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Illuminate\Support\Facades\Auth;
use Northstar\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new WebController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     * @param AuthorizationServer $oauth
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('guest:web');
        $this->middleware('throttle', ['only' => ['postRegister']]);
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        return view('auth.register', ['coverImage' => true]);
    }

    /**
     * Handle submissions of the registration form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Northstar\Exceptions\NorthstarValidationException
     */
    public function postRegister(Request $request)
    {
        $this->registrar->validate($request, null, [
            'first_name' => 'required|max:50',
            'birthdate' => 'required|date|before:now',
            'email' => 'required|email|unique:users',
            'mobile' => 'mobile|nullable|unique:users',
            'password' => 'required|min:6|max:512',
            'voter_registration_status' => 'required|in:uncertain,unregistered,confirmed',
        ]);

        // Register and login the user.
        $editableFields = $request->except(User::$internal);
        $user = $this->registrar->register($editableFields, null, function ($user) {
            // Set the user's country code by Fastly geo-location header.
            $user->country = country_code();

            // Set language based on locale (either 'en', 'es-mx')
            $user->language = app()->getLocale();

            // Sign the user up for email messaging & give them the "community" topic.
            $user->email_subscription_status = true;
            $user->email_subscription_topics = ['community'];

            // Set sms_status, if applicable
            if ($user->mobile) {
                $user->sms_status = 'active';
            }

            // Set source_detail, if applicable.
            $sourceDetail = session('source_detail');
            if ($sourceDetail) {
                $user->source_detail = stringify_object($sourceDetail);
            }

            // Exclude any 'clubs' referrals from our feature flag tests.
            if (data_get($sourceDetail, 'utm_source') !== 'clubs') {
                $feature_flags = $user->feature_flags;

                // If the badges test is running, sort users into badges group control group.
                if (config('features.badges')) {
                    // Give 70% users the badges flag (1-7), 30% in control (8-10)
                    $feature_flags['badges'] = (rand(1, 10) < 8);
                }

                // If the refer-friends test is running, give all users the refer-friends flag.
                if (config('features.refer-friends')) {
                    $feature_flags['refer-friends'] = true;
                }

                $user->feature_flags = $feature_flags;
            }
        });

        $this->cleanupSession();

        Auth::login($user, true);

        return redirect()->intended('/');
    }

    /**
     * Clean up any context we'd stored in the session during the auth flow.
     *
     * @return string
     */
    protected function cleanupSession()
    {
        $keys = [
            'authorize_client_id', 'destination', 'title',
            'callToAction', 'coverImage', 'source_detail',
        ];

        session()->forget($keys);
    }
}
