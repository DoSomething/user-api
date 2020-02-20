<?php

namespace Northstar\Policies;

use Northstar\Auth\Scope;
use Northstar\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Is the current user a staffer/administrator?
     *
     * @param User $viewer
     * @return bool
     */
    protected function isSuperuser(?User $viewer)
    {
        // If this is a machine client, it's a "superuser" even
        // though there isn't actually a _user_ authenticated:
        if (Scope::allows('admin')) {
            return true;
        }

        // But if this isn't a machine client & anonymous, they
        // certainly can't be a superuser, now can they?!
        if (! $viewer) {
            return false;
        }

        // Superusers are folks with 'admin' or 'staff' role:
        return in_array($viewer->role, ['admin', 'staff']);
    }

    /**
     * Is the current user the same person as this profile?
     *
     * @param User $viewer
     * @param User $target
     * @return bool
     */
    protected function isOwner(?User $viewer, User $target)
    {
        if (! $viewer) {
            return false;
        }

        return $viewer->is($target);
    }

    /**
     * Determine if the authorized user can see full profile details
     * for the given user account.
     *
     * @param User $viewer
     * @param User $target
     * @return bool
     */
    public function viewFullProfile(?User $viewer, User $target)
    {
        return $this->isSuperuser($viewer) || $this->isOwner($viewer, $target);
    }

    /**
     * Determine if the authorized user can edit the profile details
     * for the given user account.
     *
     * @param User $viewer
     * @param User $target
     * @return bool
     */
    public function editProfile(?User $viewer, User $target)
    {
        return $this->isSuperuser($viewer) || $this->isOwner($viewer, $target);
    }
}
