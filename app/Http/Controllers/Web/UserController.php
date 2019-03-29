<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Northstar\Auth\Registrar;
use Northstar\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UserController extends BaseController
{
    /**
     * The registrar.
     *
     * @var Registrar
     */
    protected $registrar;

    /**
     * Make a new UserController, inject dependencies and
     * set middleware for this controller's methods.
     *
     * @param Registrar $registrar
     */
    public function __construct(Registrar $registrar)
    {
        $this->registrar = $registrar;

        $this->middleware('auth:web');
        $this->middleware('role:admin,staff', ['only' => ['show']]);
    }

    /**
     * Show the homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function home()
    {
        return view('users.show', ['user' => auth()->guard('web')->user()]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // @TODO: Implement this route.
        return redirect('/');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (! $user->can('editProfile', [auth()->guard('web')->user(), $user])) {
            throw new AccessDeniedHttpException;
        }

        $defaultCountry = country_code() ?: 'US';

        return view('users.edit', compact('user', 'defaultCountry'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $user->can('editProfile', [auth()->guard('web')->user(), $user])) {
            throw new AccessDeniedHttpException;
        }

        $this->registrar->validate($request, $user, [
            'first_name' => 'required|max:50',
            'last_name' => 'nullable|max:50',
            'birthdate' => 'nullable|required|date',
            'password' => 'nullable|min:6|max:512|confirmed', // @TODO: Split into separate form.
        ]);

        // Remove fields with empty values.
        $values = array_diff($request->all(), ['']);

        $user->fill($values)->save();

        return redirect('/');
    }

    public function showChangePasswordForm(){
        return view('auth.passwords.change');
    }

    public function changePassword(Request $request){
        $user = Auth::user();

        $this->registrar->validate($request, $user, [
            'current_password' => 'required',
            'new_password' => 'nullable|min:6|max:512|confirmed',
            'new_password_confirmation' => 'required|same:new_password'
        ]);
        info('test 1');
        /*
        if (!(Hash::check($request->get('current_password'), $user->password))) {
            info('password does not match', ['password' => $user->password]);
            $this->registrar->errors()->add('current_password', 'Your current password does not matches with the password you provided. Please try again.');
        }
        info('test 2');
        if (str_is($request->get('current_password'), $request->get('new_password'))) {
            return redirect()->back()->with("error", "New Password cannot be same as your current password. Please choose a different password.");
        }
        info('test 3');
        */

        //Change Password
        $user->password = bcrypt($request->get('new_password'));
        $user->save();
        info('test 3');

        return redirect('/')->with("success", "Password changed successfully !");
    }
}
