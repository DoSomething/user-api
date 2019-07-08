<?php

namespace Northstar\Http\Controllers\Web;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ResponseInterface;
use Northstar\Auth\Entities\UserEntity;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;

class AuthController extends Controller
{
    /**
     * The OAuth authorization server.
     *
     * @var AuthorizationServer
     */
    protected $oauth;

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
    public function __construct(Registrar $registrar, AuthorizationServer $oauth)
    {
        $this->registrar = $registrar;
        $this->oauth = $oauth;

        $this->middleware('guest:web', ['only' => ['getLogin', 'postLogin', 'getRegister', 'postRegister']]);
        $this->middleware('throttle', ['only' => ['postLogin', 'postRegister']]);
    }

    /**
     * Authorize an application via OAuth 2.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface|\Illuminate\Http\RedirectResponse
     */
    public function authorize(ServerRequestInterface $request, ResponseInterface $response)
    {
        // Validate the HTTP request and return an AuthorizationRequest.
        $authRequest = $this->oauth->validateAuthorizationRequest($request);
        $client = $authRequest->getClient();

        // Store the Client ID so we can set user source on registrations.
        session(['authorize_client_id' => request()->query('client_id')]);

        // Store the referrer URI so we can redirect back to it if necessary.
        session(['referrer_uri' => request()->query('referrer_uri')]);

        if (! Auth::check()) {
            $authorizationRoute = request()->query('mode') === 'login' ? 'login' : 'register';

            session([
                'destination' => request()->query('destination', $client->getName()),
                'title' => request()->query('title', trans('auth.get_started.create_account')),
                'callToAction' => request()->query('callToAction', trans('auth.get_started.call_to_action')),
                'coverImage' => request()->query('coverImage', asset('members.jpg')),
                'source_detail' => array_filter([
                    'contentful_id' => request()->query('contentful_id'),
                    'utm_source' => request()->query('utm_source'),
                    'utm_medium' => request()->query('utm_medium'),
                    'utm_campaign' => request()->query('utm_campaign'),
                ]),
            ]);

            return redirect()->guest($authorizationRoute);
        }

        $user = UserEntity::fromModel(Auth::user());
        $authRequest->setUser($user);

        // Clients are all our own at the moment, so they will always be approved.
        // @TODO: Add an explicit "DoSomething.org app" boolean to the Client model.
        $authRequest->setAuthorizationApproved(true);

        // Return the HTTP redirect response.
        return $this->oauth->completeAuthorizationRequest($authRequest, $response);
    }

    /**
     * Show the login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle submissions of the login form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'username' => 'required',
            'password' => 'required',
        ]);

        // Check if that user needs to reset their password in order to log in.
        $user = $this->registrar->resolve(['username' => $request['username']]);
        if ($user && ! $user->hasPassword()) {
            return redirect()->back()->withInput($request->only('username'))->with('request_reset', true);
        }

        // Attempt to log in the user to Northstar!
        $credentials = $request->only('username', 'password');
        $validCredentials = Auth::validate($credentials);
        if (! $validCredentials) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'These credentials do not match our records.']);
        }

        $this->cleanupSession();

        // Finally, if the user has 2FA enabled, redirect them to the code verification
        // form (see TotpController@verify) to complete their authentication:
        if ($user->totp) {
            session(['totp.user' => $user->id]);

            return redirect('/totp');
        }

        // We've made it! Log in the user, with a "remember token", and
        // send them along to their intended destination:
        Auth::login($user, true);

        return redirect()->intended('/');
    }

    /**
     * Log a user out from Northstar, preventing one-click
     * sign-ons to other DoSomething.org websites.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLogout(Request $request)
    {
        // A custom post-logout redirect can be specified with `/logout?redirect=`
        $redirect = $request->query('redirect');

        if (! $redirect || ! is_dosomething_domain($redirect)) {
            $redirect = 'login';
        }

        Auth::logout();

        return redirect($redirect);
    }

    /**
     * Display the registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRegister()
    {
        return view('auth.register');
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
            'voter_registration_status' => 'required',
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
        });

        // If the badges test is running, sort users into badges group control group
        if (config('features.badges')) {
            $feature_flags = $user->feature_flags;

            // Give 70% users the badges flag (1-7), 30% in control (8-10)
            $feature_flags['badges'] = (rand(1, 10) < 8);

            $user->feature_flags = $feature_flags;
        }

        $this->cleanupSession();

        Auth::login($user, true);

        return redirect()->intended('/');
    }

    /**
     * Display some useful information (and a logout button!) for
     * developers using Postman/Paw to test the Auth Code flow.
     *
     * @return \Illuminate\Http\Response
     */
    public function callback()
    {
        $user = Auth::user();

        return view('auth.callback', compact('user'));
    }

    /**
     * Clean up any context we'd stored in the session during the auth flow.
     *
     * @return string
     */
    public function cleanupSession()
    {
        session()->forget('destination', 'title', 'callToAction', 'coverImage', 'source_detail');
    }
}
