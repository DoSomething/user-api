<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Http\Controllers\Controller;

class ProfileAboutController extends Controller
{
    /**
     * Add Authentication Middleware.
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');
    }

    /**
     * Display the User Details Form
     *
     */
    public function edit()
    {
        return view('profiles.about.edit', ['user' => auth()->guard('web')->user()]);
    }

    /**
     * Handle Submissions of the User Details Form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = auth()->guard('web')->user();

        $request["birthdate"] = implode("/", $request["birthdate"]);

        if(strlen($request["birthdate"] < 3))
            $request["birthdate"] = null;

        $this->registrar->validate($request, null, [
            'birthdate' => 'nullable|date|date:now',
            'email' => 'email|nullable|unique:users',
            'mobile' => 'mobile|nullable|unique:users',
        ]);

        if($request["birthdate"])
            $user->birthdate = $request["birthdate"];

        if($request["voter_registration_status"])
            $user->voter_registration_status = $request["voter_registration_status"];

        if($request["causes"])
            $user->causes = array_merge($user->causes, $request["causes"]);

        $user->save();

        return redirect(url('profile/subscriptions'));
    }
}
