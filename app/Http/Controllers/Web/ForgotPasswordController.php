<?php

namespace Northstar\Http\Controllers\Web;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Northstar\Http\Controllers\Controller;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle', ['only' => ['sendResetLinkEmail']]);
    }
}
