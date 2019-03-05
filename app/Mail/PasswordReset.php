<?php

namespace Northstar\Mail;

use Illuminate\Mail\Mailable;
use Northstar\PasswordResetType;

class PasswordReset extends Mailable
{
    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $token
     * @param string $type
     * @return void
     */
    public function __construct($user, $token, $type)
    {
        $this->token = $token;
        $this->type = $type;
        $this->user = $user;
        $this->url = route('password.reset', [
            $this->token,
            'email' => $user->email,
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

    /**
     * Transform the message for Blink.
     *
     * @return $array
     */
    public function toCustomerIoPayload()
    {
        return [
            'body' => $this->render(),
            'subject' => $this->subject,
            'type' => $this->type,
            'url' => $this->url,
            'user_id' => $this->user->id,
        ];
    }
}
