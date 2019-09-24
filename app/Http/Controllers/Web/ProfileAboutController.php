<?php

namespace Northstar\Http\Controllers\Web;

// use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Http\Controllers\Controller;

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
     * @return \Illuminate\Http\Response
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
        // @TODO: finish adding validation and appending data to the user
        // notes for monday:
        // concantenate dates together into a single string & validate
        // add conditionals for whether there is a value passed from the user for each field
        // write a foreach with the causes and push them into causes if it already exists
        // save the user

        $input = $request->all();
        //user here - use to update and validate specific attributes
        $user = auth()->guard('web')->user();

        // dd([
        //     $request,
        //     $request->all(),
        //     auth()->guard('web')->user()
        // ]);

        return redirect(url('profile/subscriptions'));
    }
}
