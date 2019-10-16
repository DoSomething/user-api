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
            'causes' => ['animal_welfare', 'education', 'lgbtq_rights_equality', 'sexual_harassment_assault'],
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
            'google_id' => $user->google_id,
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
            'community_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('community', $user->email_subscription_topics) : false,
            'scholarship_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('scholarships', $user->email_subscription_topics) : false,
            'animal_welfare' => true,
            'bullying' => false,
            'education' => true,
            'environment' => false,
            'gender_rights_equality' => false,
            'homelessness_poverty' => false,
            'immigration_refugees' => false,
            'lgbtq_rights_equality' => true,
            'mental_health' => false,
            'physical_health' => false,
            'racial_justice_equity' => false,
            'sexual_harassment_assault' => true,
            'voting_plan_status' => null,
            'voting_plan_method_of_transport' => null,
            'voting_plan_time_of_day' => null,
            'voting_plan_attending_with' => null,
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
    public function it_should_sanitize_changes()
    {
        $user = User::create();

        $user->first_name = 'Caroline';
        $user->password = 'secret';

        $changes = $user->getChanged();

        $this->assertEquals(['first_name' => 'Caroline', 'password' => '*****', 'audit' => '*****'], $changes);
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
    public function it_should_include_unsubscribed_false_in_customerio_payload_if_email_subscription_status_true()
    {
        $subscribedStatusUser = factory(User::class)->create([
            'email_subscription_status' => true,
        ]);
        $result = $subscribedStatusUser->toCustomerIoPayload();

        $this->assertTrue($result['email_subscription_status']);
        $this->assertFalse($result['unsubscribed']);
    }

    /** @test */
    public function it_should_include_unsubscribed_true_in_customerio_payload_if_email_subscription_status_false()
    {
        $unsubscribedStatusUser = factory(User::class)->create([
            'email_subscription_status' => false,
        ]);
        $result = $unsubscribedStatusUser->toCustomerIoPayload();

        $this->assertFalse($result['email_subscription_status']);
        $this->assertTrue($result['unsubscribed']);
    }

    /** @test */
    public function it_should_exclude_unsubscribed_in_customerio_payload_if_email_subscription_status_not_set()
    {
        $unknownStatusUser = factory(User::class)->create();
        $result = $unknownStatusUser->toCustomerIoPayload();

        $this->assertFalse(isset($result['email_subscription_status']));
        $this->assertFalse(isset($result['unsubscribed']));
    }
}
