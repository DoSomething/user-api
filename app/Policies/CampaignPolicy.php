<?php

namespace App\Policies;

use App\Models\Campaign;
use Illuminate\Auth\Access\HandlesAuthorization;

class CampaignPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  Illuminate\Contracts\Auth\Authenticatable $user
     * @param  App\Models\Campaign $campaign
     * @return bool
     */
    public function update($user, Campaign $campaign)
    {
        return is_staff_user();
    }
}
