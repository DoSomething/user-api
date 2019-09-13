<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Northstar\Models\User;
use Northstar\Http\Controllers\Controller;


class ProfileAboutController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');
    }
    //
    public function edit() 
    {
        return view('profiles.about.edit', ['user' => auth()->guard('web')->user()]);
    }

    public function store()
    {
        //store stuff
    }
}
