<?php

namespace Northstar\Http\Controllers\Web;

// use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Http\Controllers\Controller;
use Northstar\Http\Requests\ProfileAboutRequest;

class ProfileAboutController extends Controller
{
    /**
     * Add Authentication Middleware.
     */
    public function __construct()
    {
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
        $profile_input = $request->all();
        $input_causes = array_slice($profile_input, 6);
        $birthdate = $profile_input["month"]."/".$profile_input["day"]."/".$profile_input["year"];

        if(strlen($profile_input["month"]) > 0 && strlen($profile_input["day"]) > 0 && strlen($profile_input["year"]) > 0)
            $user->birthdate = $birthdate;

        if($profile_input["voter_registration_status"])
            $user->voter_registration_status = $profile_input["voter_registration_status"];

        if($input_causes)
                $user->causes = array_merge($user->causes, $input_causes);
            // array_push($user->causes, $profile_input->gender_rights_equality);

        dd([
            $request->all(),
            auth()->guard('web')->user()
        ]);

        return redirect(url('profile/subscriptions'));
    }
}
