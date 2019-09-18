<?php

namespace Northstar\Http\Controllers\Web;

// use Northstar\Models\User;
// use Illuminate\Http\Request;
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
     * 
     *  @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store()
    {
        //store stuff
    }
}
