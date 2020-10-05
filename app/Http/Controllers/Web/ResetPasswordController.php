<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Northstar\Auth\PasswordRules;
use Northstar\Events\PasswordUpdated;
use Northstar\PasswordResetType;
use Illuminate\Support\Facades\Auth;
use Northstar\Http\Controllers\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => PasswordRules::changePassword(request()->email),
        ];
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
            'remember_token' => Str::random(60),
        ])->save();

        // Pass along the password reset type route parameter as the source of password update.
        event(new PasswordUpdated($user, request()->type));

        $this->guard()->login($user);
    }

    /**
     * Display the password reset view for the given password reset type and token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $type
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $type, $token = null)
    {
        if (! isset($type)) {
            $type = PasswordResetType::$forgotPassword;
        }

        if (! in_array($type, PasswordResetType::all())) {
            throw new NotFoundHttpException;
        }

        $data = [
            'title' => 'Forgot Password',
            'header' => trans('auth.forgot_password.header'),
            'instructions' => trans('auth.forgot_password.instructions'),
            'new_password_field' => trans('auth.fields.new_password'),
            'confirm_new_password_field' => trans('auth.fields.confirm_new_password'),
            'new_password_submit' => trans('auth.forgot_password.submit_new_password'),
            'display_footer' => true,
        ];

        if (Str::contains($type, 'activate-account')) {
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
        $data['type'] = $type;
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
        if (Str::contains(request()->type, 'activate-account')) {
            return 'profile/about';
        }
        return config('services.phoenix.url').'/next/login';
    }
}
