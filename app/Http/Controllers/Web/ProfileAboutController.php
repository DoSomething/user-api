<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Northstar\Auth\Registrar;
use Northstar\Http\Controllers\Controller;

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
        $this->causes = ['animal_welfare'=> 'Animal Welfare', 'bullying'=>'Bullying', 'education'=>'Education', 'environment' => 'Environment', 'gender_rights_equality' => 'Gender Rights & Equality', 'homelessness_poverty'=> 'Homelessness & Poverty', 'immigration_refugees'=> 'Immigration & Refugees', 'lgbtq_rights_equality' => 'LGBTQ+ Rights & Equality', 'mental_health' => 'Mental Health', 'physical_health' => 'Physical Health', 'racial_justice_equity' => 'Racial Justice & Equity', 'sexual_harassment_assault' => 'Sexual Harassment & Assault'];
    }

    /**
     * Display the User Details Form
     */
    public function edit()
    {
        return view('profiles.about.edit', ['user' => auth()->guard('web')->user(), 'causes' => $this->causes]);
    }

    /**
     * Handle Submissions of the User Details Form
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $user = auth()->guard('web')->user();

        //creates a single string from the 3 dates inputted by the user
        $request['birthdate'] = implode('/', $request['birthdate']);

        //checks if the birthdate we create above contains an inputted date
        if (strlen($request['birthdate'] < 3)) {
            $request['birthdate'] = null;
        }

        $this->registrar->validate($request, null, [
            'birthdate' => 'required|date|before:now',
            'email' => 'email|nullable|unique:users',
            'mobile' => 'mobile|nullable|unique:users',
        ]);

        $completedFields = array_filter($request->all());

        $user->fill($completedFields);

        $user->save();

        return redirect(url('profile/subscriptions'));
    }
}
