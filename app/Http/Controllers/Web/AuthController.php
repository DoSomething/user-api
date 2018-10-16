<?php

namespace Northstar\Http\Controllers\Web;

use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Psr\Http\Message\ResponseInterface;
use Northstar\Auth\Entities\UserEntity;
use Psr\Http\Message\ServerRequestInterface;
use League\OAuth2\Server\AuthorizationServer;
use Illuminate\Contracts\Auth\Factory as Auth;
use Illuminate\Routing\Controller as BaseController;
use Northstar\Exceptions\NorthstarValidationException;
use Illuminate\Foundation\Validation\ValidatesRequests;

class AuthController extends BaseController
{
    use ValidatesRequests;

    /**
     * The OAuth authorization server.
     *
     * @var AuthorizationServer
     */
    protected $oauth;

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
     * Make a new WebController, inject dependencies,
     * and set middleware for this controller's methods.
     *
     * @param Auth $auth
     * @param Registrar $registrar
     * @param AuthorizationServer $oauth
     */
    public function __construct(Auth $auth, Registrar $registrar, AuthorizationServer $oauth)
    {
        $this->auth = $auth;
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

        if (! $this->auth->guard('web')->check()) {
            $authorizationRoute = request()->query('mode') === 'login' ? 'login' : 'register';

            session([
                'destination' => request()->query('destination', $client->getName()),
                'title' => request()->query('title', trans('auth.get_started.create_account')),
                'callToAction' => request()->query('callToAction', trans('auth.get_started.call_to_action')),
                'coverImage' => request()->query('coverImage', asset('members.jpg')),
            ]);

            return redirect()->guest($authorizationRoute);
        }

        $user = UserEntity::fromModel($this->auth->guard('web')->user());
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
        if (! $this->auth->guard('web')->attempt($credentials, true)) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors([
                    $this->loginUsername() => 'These credentials do not match our records.',
                ]);
        }

        // If we had stored a destination name, reset it.
        session()->pull('destination');

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

        $this->auth->guard('web')->logout();

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
     * @throws NorthstarValidationException
     */
    public function postRegister(Request $request)
    {
        $this->registrar->validate($request, null, [
            'first_name' => 'required|max:50',
            'birthdate' => 'required|date|before:now',
            'email' => 'required|email|unique:users',
            'mobile' => 'mobile|nullable|unique:users',
            'password' => 'required|min:6|max:512',
        ]);

        // Register and login the user.
        $editableFields = $request->except(User::$internal);
        $user = $this->registrar->register($editableFields, null, function ($user) {
            // Set the user's country code by Fastly geo-location header.
            $user->country = country_code();

            // Set language based on locale (either 'en', 'es-mx')
            $user->language = app()->getLocale();

            // Set sms_status, if applicable
            if ($user->mobile) {
                $user->sms_status = 'active';
            }
        });

        $this->auth->guard('web')->login($user, true);

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
        $user = $this->auth->guard('web')->user();

        return view('auth.callback', compact('user'));
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function loginUsername()
    {
        return 'username';
    }
}
