<?php

namespace App\Http\Controllers\Web;

use App\Auth\Registrar;
use App\Http\Controllers\Controller;
use App\Types\CauseInterestType;
use Illuminate\Http\Request;

class ProfileAboutController extends Controller
{
    /**
     * Add Authentication Middleware.
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('auth:web');

        $this->causes = CauseInterestType::labels();
    }

    /**
     * Display the User Details Form.
     */
    public function edit()
    {
        return view('profiles.about.edit', [
            'user' => auth()
                ->guard('web')
                ->user(),
            'causes1' => array_slice(
                $this->causes,
                0,
                count($this->causes) / 2,
            ),
            'causes2' => array_slice($this->causes, count($this->causes) / 2),
            'index1' => 0,
            'index2' => 6,
        ]);
    }

    /**
     * Handle Submissions of the User Details Form.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = auth()
            ->guard('web')
            ->user();

        $this->registrar->validate($request, null, [
            'birthdate' => 'nullable|date|before:now',
            'email' => 'email|nullable|unique:mongodb.users',
            'mobile' => 'mobile|nullable|unique:mongodb.users',
        ]);

        $completedFields = array_filter($request->all());

        $user->fill($completedFields);

        $user->save();

        return redirect(url('profile/subscriptions'));
    }
}
