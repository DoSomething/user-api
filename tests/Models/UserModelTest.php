<?php

namespace Tests\Models;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    /** @test */
    public function it_should_send_new_users_to_customer_io()
    {
        config(['features.customer_io' => true]);

        /** @var User $user */
        $user = factory(User::class)->create([
            'birthdate' => '1/2/1990',
            'email_subscription_status' => true,
            'email_subscription_topics' => ['community'],
            'sms_subscription_topics' => ['general'],
            'school_id' => '12500012',
            'causes' => [
                'animal_welfare',
                'education',
                'lgbtq_rights_equality',
                'sexual_harassment_assault',
            ],
        ]);

        // We should have made one "update" request to Customer.io when creating this user.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->once();

        // The Customer.io payload should be serialized correctly:
        $expected = [
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
            'email_subscription_status' => true,
            'unsubscribed' => false,
            'facebook_id' => $user->facebook_id,
            'google_id' => $user->google_id,
            'addr_city' => $user->addr_city,
            'addr_state' => $user->addr_state,
            'addr_zip' => $user->addr_zip,
            'country' => $user->country,
            'club_id' => $user->club_id,
            'school_id' => '12500012',
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

            // These boolean fields are computed based on whether or not array values exist:
            'news_email_subscription_status' => false,
            'lifestyle_email_subscription_status' => false,
            'community_email_subscription_status' => true,
            'scholarship_email_subscription_status' => false,
            'clubs_email_subscription_status' => false,
            'general_sms_subscription_status' => true,
            'voting_sms_subscription_status' => false,
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

            'voting_method' => null,
            'voting_plan_status' => null,
            'voting_plan_method_of_transport' => null,
            'voting_plan_time_of_day' => null,
            'voting_plan_attending_with' => null,

            'signup_badge' => false,
            'one_post_badge' => false,
            'two_posts_badge' => false,
            'three_posts_badge' => false,
            'four_posts_badge' => false,
            'news_subscription_badge' => false,
            'one_staff_fave_badge' => false,
            'two_staff_faves_badge' => false,
            'three_staff_faves_badge' => false,
        ];

        $this->assertEquals($expected, $user->toCustomerIoPayload());
    }

    /** @test */
    public function it_should_send_updated_users_to_customer_io()
    {
        config(['features.customer_io' => true]);

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

        $this->assertEquals(
            [
                'first_name' => 'Caroline',
                'password' => '*****',
                'audit' => '*****',
            ],
            $changes,
        );
    }

    /** @test */
    public function it_should_return_password_reset_url_with_token()
    {
        $email = 'forgetful@example.com';
        $token = 'd858c12a87cd43eafc24cc04bf0e06ddd2da6b7457e03ce093b3';
        $type = 'forgot-password';

        $user = factory(User::class)->create(['email' => $email]);

        $result = $user->getPasswordResetUrl($token, $type);

        $this->assertEquals(
            $result,
            route('password.reset', [
                $token,
                'email' => $email,
                'type' => $type,
            ]),
        );
    }

    /** @test */
    public function it_should_include_unsubscribed_false_in_customerio_payload_if_email_subscription_status_true()
    {
        $subscribedStatusUser = factory(User::class)
            ->states('email-subscribed')
            ->create();

        $result = $subscribedStatusUser->toCustomerIoPayload();

        $this->assertTrue($result['email_subscription_status']);
        $this->assertFalse($result['unsubscribed']);
    }

    /** @test */
    public function it_should_include_unsubscribed_true_in_customerio_payload_if_email_subscription_status_false()
    {
        $unsubscribedStatusUser = factory(User::class)
            ->states('email-unsubscribed')
            ->create();

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

    /** @test */
    public function testCustomerIoPhoneIsNotSetIfNotSubscribedToSms()
    {
        $user = factory(User::class)
            ->states('sms-unsubscribed')
            ->create();

        $result = $user->toCustomerIoPayload();

        $this->assertNull($result['phone']);
    }

    /** @test */
    public function testIsSmsSubscribedisTrueIfSmsStatusIsActive()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'active',
        ]);

        $this->assertTrue($user->isSmsSubscribed());
    }

    /** @test */
    public function testIsSmsSubscribedisTrueIfSmsStatusIsLess()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'less',
        ]);

        $this->assertTrue($user->isSmsSubscribed());
    }

    /** @test */
    public function testIsSmsSubscribedisTrueIfSmsStatusIsPending()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'pending',
        ]);

        $this->assertTrue($user->isSmsSubscribed());
    }

    /** @test */
    public function testIsSmsSubscribedisFalseIfSmsStatusIsNull()
    {
        $user = factory(User::class)->create([
            'sms_status' => null,
        ]);

        $this->assertFalse($user->isSmsSubscribed());
    }

    /** @test */
    public function testIsSmsSubscribedisFalseIfSmsStatusIsStop()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'stop',
        ]);

        $this->assertFalse($user->isSmsSubscribed());
    }

    /** @test */
    public function testIsSmsSubscribedisFalseIfMobileIsNull()
    {
        $user = factory(User::class)->create([
            'mobile' => null,
            'sms_status' => 'active',
        ]);

        $this->assertFalse($user->isSmsSubscribed());
    }

    /** @test */
    public function testHasSmsSubscriptionTopicsisTrueIfTopicsExist()
    {
        $user = factory(User::class)
            ->states('sms-subscribed')
            ->create();

        $this->assertTrue($user->hasSmsSubscriptionTopics());
    }

    /** @test */
    public function testHasSmsSubscriptionTopicsisFalseIfTopicsIsNull()
    {
        $user = factory(User::class)
            ->states('sms-unsubscribed')
            ->create();

        $this->assertFalse($user->hasSmsSubscriptionTopics());
    }

    /** @test */
    public function addsDefaultSmsSubscriptionTopicsIfSubscribed()
    {
        // By default our factory creates with SMS status active or less.
        $subscribedUser = factory(User::class)->create();

        $this->assertEquals($subscribedUser->sms_subscription_topics, [
            'general',
            'voting',
        ]);
    }

    /** @test */
    public function addsEmptySmsSubscriptionTopicsIfUnsubscribed()
    {
        $unsubscribedUser = factory(User::class)->create([
            'sms_status' => 'stop',
        ]);

        $this->assertEquals($unsubscribedUser->sms_subscription_topics, null);
    }

    /** @test */
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

    /** @test */
    public function addsDefaultSmsSubscriptionTopicsIfChangingToSubscribed()
    {
        $user = factory(User::class)->create([
            'sms_status' => 'stop',
        ]);

        $user->sms_status = 'active';
        $user->save();

        $this->assertEquals($user->sms_subscription_topics, [
            'general',
            'voting',
        ]);
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
        $this->graphqlMock
            ->shouldReceive('getClubById')
            ->with($newClubId)
            ->andReturn([
                'name' => $newClubName,
                'leaderId' => $clubLeader->id,
            ]);
        $this->graphqlMock->shouldReceive('getSchoolById')->andReturn(null);

        // Ensure that when we look up the Club Leader our defined mock is returned.
        $this->mock(User::class)
            ->shouldReceive('find')
            ->andReturn($clubLeader);

        $eventPayload = $user->getClubIdUpdatedEventPayload($newClubId);

        // Our event payload attributes should contain the club and club leader values.
        $this->assertEquals($eventPayload['club_name'], $newClubName);
        $this->assertEquals(
            $eventPayload['club_leader_first_name'],
            $clubLeader->first_name,
        );
        $this->assertEquals(
            $eventPayload['club_leader_display_name'],
            $clubLeader->display_name,
        );
        $this->assertEquals(
            $eventPayload['club_leader_email'],
            $clubLeader->email,
        );

        $user->club_id = $newClubId;
        $user->save();

        $this->assertCustomerIoEvent($user, 'club_id_updated');
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
        $this->graphqlMock
            ->shouldReceive('getClubById', 'getSchoolById')
            ->andReturn(null);

        // The Customer.io event shoud not be dispatched.
        $this->customerIoMock->shouldNotReceive('trackEvent');

        $user->club_id = 123;
        $user->save();
    }

    /** @test */
    public function it_should_sanitize_user_input()
    {
        $evildoer = factory(User::class)->create([
            'first_name' => '<a href="evil.com">click here</a>',
        ]);

        $payload = $evildoer->toCustomerIoPayload();

        $this->assertEquals('click here', $payload['first_name']);
    }

    /** @test */
    public function testSettingPromotionsMutedAt()
    {
        $user = factory(User::class)->create();

        $user->promotions_muted_at = Carbon::now();
        $user->save();

        $user->last_name = 'Morales';
        $user->save();

        // Only one update request should have been made, upon creating the user.
        $this->customerIoMock->shouldHaveReceived('updateCustomer')->once();
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->once();
    }

    /** @test */
    public function testMutingAndUnmutingPromotionsViaEmailStatusChange()
    {
        // Creating subscribed user should trigger a Customer.io update.
        $user = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => ['lifestyle', 'scholarships'],
            'sms_status' => null,
        ]);

        // Unsubscribing from all platforms should delete Customer.io profile.
        $user->email_subscription_status = false;
        $user->save();

        $this->assertNotNull($user->promotions_muted_at);

        // Resubscribing should re-create Customer.io profile.
        $user->email_subscription_topics = ['lifestyle'];
        $user->save();

        $this->assertNull($user->promotions_muted_at);

        $this->customerIoMock->shouldHaveReceived('updateCustomer')->twice();
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->once();
        $this->assertCustomerIoEvent($user, 'promotions_resubscribe');
    }

    /** @test */
    public function testMutingAndUnmutingPromotionsViaSmsStatusChange()
    {
        // Creating subscribed user should trigger a Customer.io update.
        $user = factory(User::class)
            ->states('sms-subscribed')
            ->create([
                'email_subscription_status' => null,
            ]);

        // Unsubscribing from all platforms should delete Customer.io profile.
        $user->sms_status = 'stop';
        $user->save();

        $this->assertNotNull($user->promotions_muted_at);

        // Resubscribing should re-create Customer.io profile.
        $user->sms_status = 'less';
        $user->save();

        $this->assertNull($user->promotions_muted_at);

        $this->customerIoMock->shouldHaveReceived('updateCustomer')->twice();
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->once();
        $this->assertCustomerIoEvent($user, 'promotions_resubscribe');
    }

    /** @test */
    public function testUnmutingPromotionsViaEmailTopicsChange()
    {
        // Creating subscribed user should trigger a Customer.io update.
        $user = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => ['lifestyle'],
            'sms_status' => null,
        ]);

        // Manually mute promotions for the user.
        $user->promotions_muted_at = Carbon::now();
        $user->save();

        $this->assertNotNull($user->promotions_muted_at);

        // Updating topics should re-create Customer.io profile.
        $user->email_subscription_topics = ['scholarships', 'community'];
        $user->save();

        $this->assertNull($user->promotions_muted_at);

        $this->customerIoMock->shouldHaveReceived('updateCustomer')->twice();
        $this->customerIoMock->shouldHaveReceived('deleteCustomer')->once();
        $this->assertCustomerIoEvent($user, 'promotions_resubscribe');
    }

    /** @test */
    public function testTrackCustomerIoEventForUnsubscribedUser()
    {
        // Creating subscribed user should trigger a Customer.io update.
        $user = factory(User::class)
            ->states('email-unsubscribed')
            ->create([
                'sms_status' => null,
            ]);

        $user->trackCustomerIoEvent('test_event', ['foo' => 'bar']);

        $this->customerIoMock->shouldNotReceive('trackEvent');
        $this->customerIoMock->shouldNotReceive('updateCustomer');
    }

    /** @test */
    public function testTrackCustomerIoEventForSubscribedUser()
    {
        // Creating subscribed user should trigger a Customer.io update.
        $user = factory(User::class)
            ->states('email-subscribed')
            ->create([
                'sms_status' => null,
            ]);

        $user->trackCustomerIoEvent('test_event', ['foo' => 'bar']);

        $this->assertCustomerIoEvent($user, 'test_event');
        $this->customerIoMock->shouldNotReceive('updateCustomer');
    }

    /** @test */
    public function testTrackCustomerIoEventForMutedPromotionsSubscribedUser()
    {
        $user = factory(User::class)
            ->states('email-subscribed')
            ->create([
                'sms_status' => null,
            ]);
        //  Manually mute promotions.
        $user->email_subscription_status = Carbon::now();
        $user->save();

        $user->trackCustomerIoEvent('test_event', ['foo' => 'bar']);

        // Verify promotions are no longer muted.
        $this->assertNull($user->promotions_muted_at);
        $this->assertCustomerIoEvent($user, 'test_event');
    }
}
