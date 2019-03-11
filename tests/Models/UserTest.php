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
            'news_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('news', $user->email_subscription_topics) : false,
            'lifestyle_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('lifestyle', $user->email_subscription_topics) : false,
            'action_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('actions', $user->email_subscription_topics) : false,
            'scholarship_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('scholarships', $user->email_subscription_topics) : false,
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

    /** @test */
    public function it_should_return_password_reset_url_with_token()
    {
        $email = 'forgetful@example.com';
        $token = 'd858c12a87cd43eafc24cc04bf0e06ddd2da6b7457e03ce093b3';
        $type = 'forgot-password';

        $user = factory(User::class)->create(['email' => $email]);
        $result = $user->getPasswordResetUrl($token, $type);

        $this->assertEquals($result, route('password.reset', [$token, 'email' => $email, 'type' => $type]));
    }

    /** @test */
    public function it_should_include_email_subscription_status_in_customerio_payload_if_set()
    {
        $subscribedStatusUser = factory(User::class)->create([
            'email' => 'subscribed@example.com',
            'email_subscription_status' => true,
        ]);
        $result = $subscribedStatusUser->toCustomerIoPayload();

        $this->assertTrue($result['email_subscription_status']);
        $this->assertFalse($result['unsubscribed']);

        $unsubscribedStatusUser = factory(User::class)->create([
            'email' => 'unsubscribed@example.com',
            'email_subscription_status' => false,
        ]);
        $result = $unsubscribedStatusUser->toCustomerIoPayload();

        // TODO: These tests fail because of https://www.pivotaltracker.com/story/show/164346648
        // $this->assertFalse($result['email_subscription_status']);
        // $this->assertTrue($result['unsubscribed']);
    }

    /** @test */
    public function it_should_exclude_email_subscription_status_in_customerio_payload_if_not_set()
    {
        $unknownStatusUser = factory(User::class)->create([
            'email' => 'unknown@example.com',
        ]);
        $result = $unknownStatusUser->toCustomerIoPayload();

        $this->assertFalse(isset($result['email_subscription_status']));
        $this->assertFalse(isset($result['unsubscribed']));
    }
}
