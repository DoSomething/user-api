<?php

use Northstar\Models\User;
use Northstar\Services\GraphQL;
use Northstar\Services\CustomerIo;

class UserModelTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_send_new_users_to_customer_io()
    {
        config(['features.blink' => true]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'birthdate' => '1/2/1990',
            'causes' => ['animal_welfare', 'education', 'lgbtq_rights_equality', 'sexual_harassment_assault'],
        ]);

        // We should have made one "update" request to Customer.io when creating this user.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->once();

        // The Customer.io payload should be serialized correctly:
        $this->assertEquals($user->toCustomerIoPayload(), [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'display_name' => $user->display_name,
            'last_name' => null,
            'birthdate' => '631238400',
            'email' => $user->email,
            'phone' => $user->mobile,
            'sms_status' => $user->sms_status,
            'sms_paused' => (bool) $user->sms_paused,
            'sms_status_source' => 'northstar',
            'facebook_id' => $user->facebook_id,
            'google_id' => $user->google_id,
            'addr_city' => $user->addr_city,
            'addr_state' => $user->addr_state,
            'addr_zip' => $user->addr_zip,
            'country' => $user->country,
            'school_id' => $user->school_id,
            'club_id' => $user->club_id,
            'school_name' => 'San Dimas High School',
            'school_state' => 'CA',
            'voter_registration_status' => $user->voter_registration_status,
            'language' => $user->language,
            'source' => $user->source,
            'source_detail' => $user->source_detail,
            'referrer_user_id' => $user->referrer_user_id,
            'deletion_requested_at' => null,
            'last_authenticated_at' => null,
            'last_messaged_at' => null,
            'updated_at' => $user->updated_at->timestamp,
            'created_at' => $user->created_at->timestamp,
            'news_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('news', $user->email_subscription_topics) : false,
            'lifestyle_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('lifestyle', $user->email_subscription_topics) : false,
            'community_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('community', $user->email_subscription_topics) : false,
            'scholarship_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('scholarships', $user->email_subscription_topics) : false,
            'clubs_email_subscription_status' => isset($user->email_subscription_topics) ? in_array('clubs', $user->email_subscription_topics) : false,
            'general_sms_subscription_status' => isset($user->sms_subscription_topics) ? in_array('general', $user->sms_subscription_topics) : false,
            'voting_sms_subscription_status' => isset($user->sms_subscription_topics) ? in_array('voting', $user->sms_subscription_topics) : false,
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
    public function it_should_send_updated_users_to_customer_io()
    {
        config(['features.blink' => true]);

        /** @var User $user */
        $user = factory(User::class)->create();
        $user->update(['birthdate' => '1/15/1990']);

        // We should have made one request to Customer.io when we created this
        // user, and a second "update" request when we called 'update()'.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->twice();
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
        $subscribedStatusUser = factory(User::class)->states('email-subscribed')->create();
        $result = $subscribedStatusUser->toCustomerIoPayload();

        $this->assertTrue($result['email_subscription_status']);
        $this->assertFalse($result['unsubscribed']);
    }

    /** @test */
    public function it_should_include_unsubscribed_true_in_customerio_payload_if_email_subscription_status_false()
    {
        $unsubscribedStatusUser = factory(User::class)->states('email-unsubscribed')->create();
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

    public function testIsSmsSubscribedisTrueIfSmsStatusIsActive()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'active',
        ]);

        $this->assertTrue($user->isSmsSubscribed());
    }

    public function testIsSmsSubscribedisTrueIfSmsStatusIsLess()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'less',
        ]);

        $this->assertTrue($user->isSmsSubscribed());
    }

    public function testIsSmsSubscribedisFalseIfSmsStatusIsNull()
    {
        $user = factory(User::class)->create([
            'sms_status' => null,
        ]);

        $this->assertFalse($user->isSmsSubscribed());
    }

    public function testIsSmsSubscribedisFalseIfSmsStatusIsStop()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'stop',
        ]);

        $this->assertFalse($user->isSmsSubscribed());
    }

    public function testHasSmsSubscriptionTopicsisTrueIfTopicsExist()
    {
        $user = factory(User::class)->states('sms-subscribed')->create();

        $this->assertTrue($user->hasSmsSubscriptionTopics());
    }

    public function testHasSmsSubscriptionTopicsisFalseIfTopicsIsNull()
    {
        $user = factory(User::class)->states('sms-unsubscribed')->create();

        $this->assertFalse($user->hasSmsSubscriptionTopics());
    }

    public function addsDefaultSmsSubscriptionTopicsIfSubscribed()
    {
        // By default our factory creates with SMS status active or less.
        $subscribedUser = factory(User::class)->create();

        $this->assertEquals($subscribedUser->sms_subscription_topics, ['general', 'voting']);
    }

    public function addsEmptySmsSubscriptionTopicsIfUnsubscribed()
    {
        $unsubscribedUser = factory(User::class)->create([
            'sms_status' => 'stop',
        ]);

        $this->assertEquals($subscribedUser->sms_subscription_topics, []);
    }

    public function doesNotChangeSubscriptionTopicsIfExistsWhenChangingSubscribedStatus()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'less',
            'sms_subscription_topics' => ['voting'],
        ]);

        $user->sms_status = 'active';
        $user->save();

        $this->assertEquals($user->sms_subscription_topics, ['voting']);
    }

    public function addsDefaultSmsSubscriptionTopicsIfChangingToSubscribed()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'stop',
        ]);

        $user->sms_status = 'active';
        $user->save();

        $this->assertEquals($user->sms_subscription_topics, ['general', 'voting']);
    }

    /**
     * Test that an event is dispatched to Customer.io with the expected attributes when a user's club_id is updated.
     *
     * @return void
     */
    public function testUpdatingClubId()
    {
        $user = factory(User::class)->create();
        $clubLeader = factory(User::class)->create();

        $newClubId = 2;
        $newClubName = 'DoSomething Staffers Club';

        // Ensure we query this Rogue club via GraphQL.
        $this->graphqlMock = $this->mock(GraphQL::class);
        $this->graphqlMock->shouldReceive('getClubById')->with($newClubId)->andReturn([
            'name' => $newClubName,
            'leaderId' => $clubLeader->id,
        ]);
        $this->graphqlMock->shouldReceive('getSchoolById')->andReturn(null);

        // Ensure that when we look up the Club Leader our defined mock is returned.
        $this->mock(User::class)->shouldReceive('find')->andReturn($clubLeader);

        $eventPayload = $user->getClubIdUpdatedEventPayload($newClubId);

        // Our event payload attributes should contain the club and club leader values.
        $this->assertEquals($eventPayload['club_name'], $newClubName);
        $this->assertEquals($eventPayload['club_leader_first_name'], $clubLeader->first_name);
        $this->assertEquals($eventPayload['club_leader_display_name'], $clubLeader->display_name);
        $this->assertEquals($eventPayload['club_leader_email'], $clubLeader->email);

        $user->update(['club_id' => $newClubId]);

        $this->customerIoMock->shouldHaveReceived('trackEvent')->once();
    }

    /**
     * Test that an event is not dispatched to Customer.io if a user is updated with a club_id with no matching club in Rogue.
     *
     * @return void
     */
    public function testUpdatingClubIdWithInvalidClub()
    {
        $user = factory(User::class)->create();

        // Ensure we don't find a Rogue club via GraphQL.
        $this->mock(GraphQL::class)->shouldReceive('getClubById', 'getSchoolById')->andReturn(null);

        // The Customer.io event shoud not be dispatched.
        $this->customerIoMock->shouldNotReceive('trackEvent');

        $user->update(['club_id' => 123]);
    }
}
