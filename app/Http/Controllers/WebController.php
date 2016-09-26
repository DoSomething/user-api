<?php

namespace Northstar\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Contracts\Auth\Factory as Auth;
use Northstar\Auth\Registrar;
use Northstar\Exceptions\NorthstarValidationException;
use Northstar\Models\User;

class WebController extends BaseController
{
    use ValidatesRequests;

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
     * @param \Illuminate\Contracts\Auth\Factory $auth
     * @param Registrar $registrar
     */
    public function __construct(Auth $auth, Registrar $registrar)
    {
        $this->auth = $auth;
        $this->registrar = $registrar;

        $this->middleware('auth:web', ['only' => ['home']]);
        $this->middleware('guest:web', ['except' => ['home', 'getLogout']]);
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('home', ['user' => auth()->guard('web')->user()]);
    }

    /**
     * Show the login form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getLogin(Request $request)
    {
        $withChrome = filter_var($request->query('chrome', 'true'), FILTER_VALIDATE_BOOLEAN);

        return view('login', ['withChrome' => $withChrome]);
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

        $credentials = $request->only('username', 'password');
        if (! $this->auth->guard('web')->attempt($credentials, true)) {
            return redirect()->back()
                ->withInput($request->only('username'))
                ->withErrors(['username' => 'These credentials do not match our records.']);
        }

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
        $redirect = $request->query('redirect', 'login');

        $this->auth->guard('web')->logout();

        return redirect($redirect);
    }

    /**
     * Display the registration form.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRegister(Request $request)
    {
        $withChrome = filter_var($request->query('chrome', 'true'), FILTER_VALIDATE_BOOLEAN);

        return view('register', ['withChrome' => $withChrome]);
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
        // If a user exists but has not set a password yet, allow them to
        // "register" to set a new password on their account.
        $existing = $this->registrar->resolve($request);
        if ($existing && $existing->hasPassword()) {
            throw new NorthstarValidationException(['email' => 'A user with that email or mobile has already been registered.']);
        }

        $this->registrar->validate($request, $existing, [
            'first_name' => 'required',
            'birthdate' => 'required|date',
            'password' => 'required|confirmed|min:6',
        ]);

        // Register and login the user.
        $user = $this->registrar->register($request->except(User::$internal), $existing);
        $this->auth->guard('web')->login($user, true);

        return redirect()->intended('/');
    }
}
