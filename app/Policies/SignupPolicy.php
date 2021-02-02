<?php

namespace App\Policies;

use App\Models\Signup;
use Illuminate\Auth\Access\HandlesAuthorization;

class SignupPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the full signup should be displayed.
     *
     * @param  Illuminate\Contracts\Auth\Authenticatable $user
     * @param  App\Models\Signup $signup
     * @return bool
     */
    public function viewAll($user, Signup $signup)
    {
        return is_staff_user() || is_owner($signup);
    }
}
