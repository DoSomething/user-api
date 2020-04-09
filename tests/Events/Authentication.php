<?php

use Carbon\Carbon;
use Northstar\Models\User;

class AuthenticationEventsTest extends BrowserKitTestCase
{
    /** @test */
    public function testSuccessfulLoginEvent()
    {
        /** @var \Northstar\Models\User $user */
        $user = factory(User::class)->create(['last_authenticated_at' => Carbon::yesterday()]);

        // Save a reference to "now" so we can compare it.
        Carbon::setTestNow($now = Carbon::now());

        // Trigger the login event!
        event(new \Illuminate\Auth\Events\Login(config('auth.defaults.guard'), $user, true));

        // The user's `last_authenticated_at` timestamp should be updated.
        $this->seeInDatabase('users', [
            '_id' => $user->id,
            'last_authenticated_at' => $now,
        ]);
    }
}
