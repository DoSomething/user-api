<?php

use Northstar\Models\User;

class UserModelTest extends BrowserKitTestCase
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
            'birthdate' => '631238400',
            'email' => $user->email,
            'mobile' => $user->mobile,
            'sms_status' => $user->sms_status,
            'sms_paused' => (bool) $user->sms_paused,
            'sms_status_source' => 'northstar',
            'facebook_id' => $user->facebook_id,
            'addr_city' => $user->addr_city,
            'addr_state' => $user->addr_state,
            'addr_zip' => $user->addr_zip,
            'country' => $user->country,
            'voter_registration_status' => $user->voter_registration_status,
            'language' => $user->language,
            'source' => $user->source,
            'source_detail' => $user->source_detail,
            'last_authenticated_at' => null,
            'last_messaged_at' => null,
            'updated_at' => $user->updated_at->toIso8601String(),
            'created_at' => $user->created_at->toIso8601String(),
            'voting_plan_status' => null,
            'voting_plan_method_of_transport' => null,
            'voting_plan_time_of_day' => null,
            'voting_plan_attending_with' => null,
            'news_email_subscription_status' => null,
            'lifestyle_email_subscription_status' => null,
            'action_email_subscription_status' => null,
            'scholarship_email_subscription_status' => null,
        ]);
    }

    /** @test */
    public function it_should_send_updated_users_to_blink()
    {
        config(['features.blink' => true]);

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->update(['birthdate' => '1/15/1990']);

        // We should have made one "create" request to Blink,
        // and a second "update" request afterwards.
        $this->blinkMock->shouldHaveReceived('userCreate')->twice();
    }

    /** @test */
    public function it_should_log_changes()
    {
        $logger = $this->spy('log');
        $user = User::create();

        $user->first_name = 'Caroline';
        $user->password = 'secret';

        // Freeze time for testing audit info.
        $time = $this->mockTime();

        $user->save();

        // Setting up audit mock example for DRYness.
        $auditMock = [
            'source' => 'northstar',
            'updated_at' => $time,
        ];

        $logger->shouldHaveReceived('debug')->once()->with('updated user', [
            'id' => $user->id,
            'changed' => [
                'first_name' => 'Caroline',
                'password' => '*****',
                'audit' => '*****',
            ],
        ]);
    }
}
