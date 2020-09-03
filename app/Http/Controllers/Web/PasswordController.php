<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Northstar\Auth\PasswordRules;
use Northstar\Events\PasswordUpdated;
use Northstar\Http\Controllers\Controller;
use Northstar\Models\User;

class PasswordController extends Controller
{
    /**
     * Make a new PasswordController, inject dependencies and
     * set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    /**
     * Update the specified user's password.
     *
     * @param  \Northstar\Models\User $user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, Request $request)
    {
        $this->authorize('editProfile', $user);

        $this->validate($request, [
            'current_password' => 'password:web',
            'password' => PasswordRules::changePassword($user->email),
        ]);

        $user->password = $request->password;
        $user->save();

        event(new PasswordUpdated($user, 'profile'));

        return redirect('/');
    }
}
