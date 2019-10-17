<?php

namespace Northstar\Http\Controllers\Web;

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
    }

    /**
     * Display the User subscriptions form.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        return view('profiles.subscriptions.edit', [
            'user' => auth()->guard('web')->user(),
            'intended' => session()->get('url.intended', '/'),
        ]);
    }

    /**
     * Handle submissions of the User subscriptions form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = auth()->guard('web')->user();

        $this->registrar->validate($request, $user);

        $currentMobile = $user->mobile;

        $this->registrar->register($request->all(), $user, function ($user) use ($currentMobile) {
            // Set the sms_status if we're adding or updating the user's mobile.
            if ($user->mobile && $user->mobile !== $currentMobile) {
                $user->sms_status = 'active';
            }
        });

        return redirect()->intended('/');
    }
}
