<?php

namespace App\Listeners;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class ReportSuccessfulAuthentication
{
    /**
     * Handle the event.
     *
     * @param Login $event
     * @return void
     * @internal param User $user
     */
    public function handle(Login $event)
    {
        /** @var User $user */
        $user = $event->user;

        // Update the user's 'last_logged_in' field.
        $user->last_authenticated_at = Carbon::now();
        $user->save();

        // Write this event to the log.
        Log::info('user_authenticated', ['id' => $user->id]);
    }
}
