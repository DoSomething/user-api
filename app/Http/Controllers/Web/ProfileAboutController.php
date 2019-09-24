<?php

namespace Northstar\Http\Controllers\Web;

// use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Http\Controllers\Controller;
use Northstar\Http\Requests\ProfileAboutRequest;

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
     * @param ProfileAboutRequest $request
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        // @TODO: finish adding validation and appending data to the user
        // notes for monday:
        // concantenate dates together into a single string & validate
        // add conditionals for whether there is a value passed from the user for each field
        // write a foreach with the causes and push them into causes if it already exists
        // save the user
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
