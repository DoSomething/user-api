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

        $this->causes = [
            'animal_welfare'=> 'Animal Welfare',
            'bullying'=>'Bullying',
            'education'=>'Education',
            'environment' => 'Environment',
            'gender_rights_equality' => 'Gender Rights & Equality',
            'homelessness_poverty'=> 'Homelessness & Poverty',
            'immigration_refugees'=> 'Immigration & Refugees',
            'lgbtq_rights_equality' => 'LGBTQ+ Rights & Equality',
            'mental_health' => 'Mental Health',
            'physical_health' => 'Physical Health',
            'racial_justice_equity' => 'Racial Justice & Equity',
            'sexual_harassment_assault' => 'Sexual Harassment & Assault',
        ];
    }

    /**
     * Display the User Details Form
     */
    public function edit()
    {

        return view('profiles.about.edit', [
            'user' => auth()->guard('web')->user(),
            'causes1' => array_slice($this->causes, 0, count($this->causes) / 2),
            'causes2' => array_slice($this->causes, count($this->causes) / 2),
            'index1' => 0,
            'index2' => 6,
        ]);
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

        $this->registrar->validate($request, null, [
            'birthdate' => 'nullable|date|before:now',
            'email' => 'email|nullable|unique:users',
            'mobile' => 'mobile|nullable|unique:users',
        ]);

        $completedFields = array_filter($request->all());

        $user->fill($completedFields);

        $user->save();

        return redirect(url('profile/subscriptions'));
    }
}
