<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Make a new ResetPasswordController, inject dependencies,
     * and set middleware for this controller's methods.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => $password,
            'remember_token' => str_random(60),
        ])->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    /**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
        $data = [
            'title' => 'Forgot Password',
            'header' => trans('auth.forgot_password.header'),
            'instructions' => trans('auth.forgot_password.instructions'),
            'new_password_field' => trans('auth.fields.new_password'),
            'confirm_new_password_field' => trans('auth.fields.confirm_new_password'),
            'new_password_submit' => trans('auth.forgot_password.submit_new_password'),
            'display_footer' => true,
        ];

        if (str_contains(request()->input('type'), 'activate-account')) {
            $data = [
                'title' => 'Activate Account',
                'header' => trans('auth.activate_account.header'),
                'instructions' => trans('auth.activate_account.instructions'),
                'new_password_field' => trans('auth.fields.password'),
                'confirm_new_password_field' => trans('auth.fields.confirm_password'),
                'new_password_submit' => trans('auth.activate_account.submit_new_password'),
                'display_footer' => false,
            ];
        }

        $data['token'] = $token;
        $data['email'] = $request->email;

        return view('auth.passwords.reset')->with($data);
    }

    /**
     * Get the guard to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('web');
    }

    /**
     * Get the path to redirect to after resetting a password.
     *
     * @return string
     */
    public function redirectPath()
    {
        return config('services.phoenix.url').'/next/login';
    }
}
