<?php

namespace App\Http\Controllers\Web;

use App\Auth\PasswordRules;
use App\Events\PasswordUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
     * @param  \App\Models\User $user
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
