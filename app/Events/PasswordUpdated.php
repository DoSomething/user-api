<?php

namespace Northstar\Events;

use Illuminate\Queue\SerializesModels;

class PasswordUpdated
{
    use SerializesModels;
    /**
     * The user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * Source of password update (either profile or type of reset password form).
     *
     * @var string
     */
    public $source;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string
     * @return void
     */
    public function __construct($user, $source)
    {
        $this->user = $user;
        $this->source = $source;
    }
}
