<?php

namespace App\Http\Controllers\Web;

use App\Auth\Registrar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
            'user' => auth()
                ->guard('web')
                ->user(),
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
        $user = auth()
            ->guard('web')
            ->user();

        $this->registrar->validate($request, $user);

        $currentMobile = $user->mobile;

        // Make sure that we wipe out existing email topics if the form is submitted with none checked
        $request = $request->all();
        if (!array_key_exists('email_subscription_topics', $request)) {
            $request['email_subscription_topics'] = [];
        }

        $this->registrar->register($request, $user, function (
            $user
        ) use ($currentMobile) {
            // Set the sms_status if we're adding or updating the user's mobile.
            if ($user->mobile && $user->mobile !== $currentMobile) {
                if ($user->sms_status !== 'less') {
                    $user->sms_status = 'active';
                }
            }
        });

        return redirect()->intended('/');
    }
}
