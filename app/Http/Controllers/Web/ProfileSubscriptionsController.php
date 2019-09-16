<?php

namespace Northstar\Http\Controllers\Web;

// use Northstar\Models\User;
// use Illuminate\Http\Request;
use Northstar\Http\Controllers\Controller;

class ProfileSubscriptionsController extends Controller
{
    // @TODO: Add DocBlock.
    public function __construct()
    {
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');
    }

    // @TODO: Add DocBlock.
    public function edit()
    {
        return view('profiles.subscriptions.edit', ['user' => auth()->guard('web')->user()]);
    }

    // @TODO: Add DocBlock.
    public function store()
    {
        //store stuff
    }
}
