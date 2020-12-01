<?php

use Northstar\Models\User;

class SubscriptionsTest extends BrowserKitTestCase
{
    /**
     * Test adding a subscription topic to an existing user.
     * POST /v2/subscriptions.
     *
     * @return void
     */
    public function testAddSubscriptionToExistingUser()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => [],
        ]);

        $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $this->assertResponseStatus(200);

        // The email_subscription_topics should be added, but the source and source_detail should not change
        $this->seeInDatabase('users', [
            'email' => $user->email,
            'email_subscription_status' => true,
            'email_subscription_topics' => ['scholarships'],
            'source' => $user->source,
            'source_detail' => $user->source_detail,
        ]);
    }

    /**
     * Test adding a subscription topics to an existing user with no duplicates.
     * POST /v2/subscriptions.
     *
     * @return void
     */
    public function testAddSubscriptionToExistingUserWithNoDuplicates()
    {
        // Create a user who already has a subscription topic
        $user = factory(User::class)->create([
            'email_subscription_topics' => ['news'],
        ]);

        $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => 'news',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $this->assertResponseStatus(200);

        // The email_subscription_topics should have no duplicates
        $this->seeInDatabase('users', [
            'email' => $user->email,
            'email_subscription_topics' => ['news'],
            'source' => $user->source,
            'source_detail' => $user->source_detail,
        ]);
    }

    /**
     * Test adding a subscription topic to a new user.
     * POST /v2/subscriptions.
     *
     * @return void
     */
    public function testAddSubscriptionToNewUser()
    {
        $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics@dosomething.org',
            'email_subscription_topic' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $this->assertResponseStatus(201);

        // The user should be created with the given email_subscription_topics, source, and source_detail
        $this->seeInDatabase('users', [
            'email' => 'topics@dosomething.org',
            'email_subscription_status' => true,
            'email_subscription_topics' => ['scholarships'],
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);
    }

    /**
     * Test that a new user gets a password reset email.
     * POST /v2/subscriptions.
     *
     * @return void
     */
    public function testNewUserGetsActivateAccountEmail()
    {
        $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics@dosomething.org',
            'email_subscription_topic' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $this->assertResponseStatus(201);
        $this->customerIoMock->shouldHaveReceived('trackEvent')->once();

        $this->seeInDatabase('password_resets', [
            'email' => 'topics@dosomething.org',
        ]);
    }

    /**
     * Test rate limiting (10 posts per hour)
     * POST /v2/subscriptions.
     *
     * @return void
     */
    public function testSubscriptionsThrottle()
    {
        // Post to /subscriptions 10 times
        for ($i = 0; $i < 10; $i++) {
            $this->json('POST', 'v2/subscriptions', [
                'email' => 'topics' . $i . '@dosomething.org',
                'email_subscription_topic' => 'news',
                'source' => 'phoenix-next',
                'source_detail' => 'test_source_detail',
            ]);

            $this->assertResponseStatus(201);
        }

        // Get a "Too Many Attempts" response when trying for an 11th post within an hour
        $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics11@dosomething.org',
            'email_subscription_topics' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $this->assertResponseStatus(429);
    }
}
