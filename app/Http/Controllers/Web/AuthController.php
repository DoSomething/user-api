<?php

namespace Northstar\Http\Controllers\Web;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Auth\PasswordRules;
use Illuminate\Support\Facades\Auth;
use Psr\Http\Message\ResponseInterface;
use Northstar\Auth\Entities\UserEntity;
use Northstar\Http\Controllers\Controller;
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
    public function getAuthorize(ServerRequestInterface $request, ResponseInterface $response)
    {
        $authRequest = $this->oauth->validateAuthorizationRequest($request);
        $client = $authRequest->getClient();

        if (! Auth::check()) {
            // Store any context we'll need for login or registration in the session.
            // NOTE: Be sure to clear these in AuthController@cleanupSession afterwards!
            session([
                // Store the Client ID so we can set user's source field on registrations:
                'authorize_client_id' => $client->getIdentifier(),
                // Store destination & content fields so we can customize registration page:
                'destination' => request()->query('destination', $client->getName()),
                'title' => request()->query('title', trans('auth.get_started.create_account')),
                'callToAction' => request()->query('callToAction', trans('auth.get_started.call_to_action')),
                'coverImage' => request()->query('coverImage', asset('members.jpg')),
                // Store any provided UTMs or Contentful ID for user's source_detail:
                'source_detail' => array_filter([
                    'contentful_id' => request()->query('contentful_id'),
                    'utm_source' => request()->query('utm_source'),
                    'utm_medium' => request()->query('utm_medium'),
                    'utm_campaign' => request()->query('utm_campaign'),
                ]),
                // Store the provided Referrer User ID for user's referrer_user_id field:
                'referrer_user_id' => request()->query('referrer_user_id'),
            ]);

            // Optionally, we can override the default authorization page using `?mode=login`.
            $authorizationRoute = request()->query('mode') ?: 'register';

            return redirect()->guest($authorizationRoute);
        }

        $user = UserEntity::fromModel(Auth::user());
        $authRequest->setUser($user);

        // Our applications are all first-party, so they will always be approved. If we were to allow
        // third-party apps one day, we'd want to prompt the user to approve them here.
        $authRequest->setAuthorizationApproved(true);

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
        $credentials = $this->validate($request, [
            'username' => 'required',
            'password' => PasswordRules::login(),
        ]);

        // Check if the user needs to reset their password in order to log in:
        $user = $this->registrar->resolve(['username' => $request['username']]);
        if ($user && ! $user->hasPassword()) {
            return back()
                ->withInput($request->only('username'))
                ->with('request_reset', true);
        }

        // Are the given credentials valid?
        if (! Auth::validate($credentials)) {
            return back()
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
        // A custom post-logout redirect can be specified with `/logout?redirect=`.
        // If not provided (or not for a "safe" domain), redirect to the login form.
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
            'last_name' => 'required|max:50',
            'email' => 'required|email|unique:users',
            'password' => PasswordRules::register($request->email),
        ]);

        // Register and login the user.
        $editableFields = $request->except(User::$internal);
        $user = $this->registrar->registerViaWeb($editableFields);

        $this->cleanupSession();

        Auth::login($user, true);

        return redirect('profile/about');
    }

    /**
     * Display some useful information (and a logout button!) for
     * developers using Postman/Paw to test the Auth Code flow.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCallback()
    {
        return view('auth.callback', ['user' => Auth::user()]);
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
