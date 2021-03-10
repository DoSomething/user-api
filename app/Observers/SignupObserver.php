<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Signup;
use App\Services\GraphQL;

const USER_CLUB_ID_QUERY = '
    query UserClubIdQuery($userId: String!) {
        user(id: $userId) {
            clubId
        }
    }
';

class SignupObserver
{
    /**
     * Query to get the user's club_id.
     *
     * @param string $userId
     * @return array
     */
    public function queryForUser($userId)
    {
        return app(GraphQL::class)->query(USER_CLUB_ID_QUERY, [
            'userId' => $userId,
        ]);
    }

    /**
     * Handle the Signup "creating" event.
     *
     * @param  \App\Models\Signup  $signup
     *
     * @return void
     */
    public function creating(Signup $signup)
    {
        if (config('features.track_club_id') && !$signup->club_id) {
            $data = $this->queryForUser($signup->northstar_id);

            if ($club_id = data_get($data, 'user.clubId')) {
                $signup->club_id = $club_id;
            }
        }
    }

    /**
     * Handle the Signup "created" event.
     *
     * @param  \App\Models\Signup $signup
     *
     * @return void
     */
    public function created(Signup $signup)
    {
        $userId = $signup->northstar_id;
        $user = User::findOrFail($userId);
        if ($user) {
            $userSignups = $user->signups();
            if ($userSignups->count() === 1) {
                $user->addBadge('signup');
                $user->save();
            }
        }
    }

    /**
     * Handle the Signup "deleting" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleting(Signup $signup)
    {
        $signup->update([
            'why_participated' => null,
            'details' => null,
        ]);
    }
}
