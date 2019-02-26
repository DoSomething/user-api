<?php

namespace Northstar\Mail;

use Illuminate\Mail\Mailable;

class ResetPassword extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Reset Password')
            ->markdown('vendor.notifications.email')->with([
                'actionText' => 'Reset Password',
                'actionUrl' => route('password.reset', [$this->token, 'email' => $this->user->email]),
                'greeting' => 'Hello!',
                'introLines' => [
                    'You are receiving this email because we received a password reset request for your DoSomething.org account. Here is the link to reset your password:',
                ],
                'level' => 'default',
                'outroLines' => [
                    'This link will expire in 24 hours. Once you click the button above, you will be asked to reset your password on the page. If you did not request a password reset, you can ignore this email. Your password will not change and your account is safe.',
                    'If you have further questions, please reach out to help@dosomething.org.',
                ],
            ]);
    }
}
