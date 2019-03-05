<?php

namespace Northstar\Mail;

use Illuminate\Mail\Mailable;
use Northstar\PasswordResetType;

class PasswordReset extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string $token
     * @param string $type
     * @return void
     */
    public function __construct($email, $token, $type)
    {
        $this->token = $token;
        $this->url = route('password.reset', [
            $this->token,
            'email' => $email,
            'type' => $type,
        ]);
        $this->subject = PasswordResetType::getSubject($type);
        $this->body = PasswordResetType::getBody($type);
        $this->body['actionUrl'] = $this->url;
        $this->body['level'] = 'default';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject($this->subject)
            ->markdown('vendor.notifications.email')->with($this->body);
    }
}
