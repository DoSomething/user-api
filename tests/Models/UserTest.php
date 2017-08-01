<?php

use Northstar\Models\User;

class UserModelTest extends TestCase
{
    /** @test */
    public function it_should_send_new_users_to_blink()
    {
        config(['features.blink' => true]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'birthdate' => '1/2/1990',
        ]);

        // We should have made one "create" request to Blink.
        $this->blinkMock->shouldHaveReceived('userCreate')->once()->with([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'birthdate' => '1990-01-02',
            'email' => $user->email,
            'mobile' => $user->mobile,
            'mobile_status' => $user->mobilecommons_status,
            'facebook_id' => $user->facebook_id,
            'addr_city' => $user->addr_city,
            'addr_state' => $user->addr_state,
            'addr_zip' => $user->addr_zip,
            'country' => $user->country,
            'language' => $user->language,
            'source' => $user->source,
            'source_detail' => $user->source_detail,
            'last_authenticated_at' => null,
            'updated_at' => $user->updated_at->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
        ]);
    }

    /** @test */
    public function it_should_log_changes()
    {
        $logger = $this->spy('log');
        $user = User::create();

        $user->first_name = 'Caroline';
        $user->password = 'secret';
        $user->save();

        $logger->shouldHaveReceived('debug')->once()->with('updated user', [
            'id' => $user->id,
            'client_id' => 'northstar',
            'changed' => [
                'first_name' => 'Caroline',
                'password' => '*****',
            ],
        ]);
    }
}
