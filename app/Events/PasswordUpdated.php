<?php

namespace App\Events;

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
     * Where user updated their password from (e.g type of reset password form, profile).
     *
     * @var string
     */
    public $updatedVia;

    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $updatedVia
     * @return void
     */
    public function __construct($user, $updatedVia)
    {
        $this->user = $user;
        $this->updatedVia = $updatedVia;
    }
}
