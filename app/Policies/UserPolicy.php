<?php

namespace Northstar\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Northstar\Models\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the authorized user can see full profile details
     * for the given user account.
     *
     * @param User $user
     * @param User $profile
     * @return bool
     */
    public function viewFullProfile(User $user, User $profile)
    {
        return $user->id === $profile->id;
    }
}
