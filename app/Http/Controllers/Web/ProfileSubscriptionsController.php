<?php

namespace Northstar\Http\Controllers\Web;

// use Northstar\Models\User;
use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Http\Controllers\Controller;

class ProfileSubscriptionsController extends Controller
{
    /**
     * Make a new ProfileSubscriptionsController,
     * inject dependencies, and set auth middleware.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');
    }

    /**
     * Display the User subscriptions form.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        return view('profiles.subscriptions.edit', ['user' => auth()->guard('web')->user()]);
    }

    /**
     * Handle submissions of the User subscriptions form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        //update stuff
    }
}
