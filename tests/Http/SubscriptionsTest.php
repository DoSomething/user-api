<?php

namespace Tests\Http;

use App\Models\User;
use Tests\TestCase;

class SubscriptionsTest extends TestCase
{
    /**
     * Test an invalid subscription topic is not added to an existing user.
     */
    public function testInvalidSubscriptionNotAddedToUser()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => [],
        ]);

        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => 'puppetslothtips',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing(
            'users',
            ['email_subscription_topics' => ['puppetslothtips']],
            'mongodb',
        );
    }

    /**
     * Test an empty array missing topics throws a validation error.
     */
    public function testEmptySubscriptionArrayIsInvalid()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => [],
        ]);

        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => [],
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertStatus(422);
    }

    /**
     * Test adding a subscription topic to an existing user.
     *
     * @return void
     */
    public function testAddSubscriptionToExistingUser()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => [],
        ]);

        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertOk();

        // The email_subscription_topics should be added, but the source and source_detail should not change
        $this->assertMongoDatabaseHas('users', [
            'email' => $user->email,
            'email_subscription_status' => true,
            'email_subscription_topics' => ['scholarships'],
            'source' => $user->source,
            'source_detail' => $user->source_detail,
        ]);
    }

    /**
     * Test adding multiple subscription topics to an existing user.
     *
     * @return void
     */
    public function testAddMultipleSubscriptionsToExistingUser()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => [],
        ]);

        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => ['news', 'clubs', 'community'],
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertOk();

        // The email_subscription_topics should be added, but the source and source_detail should not change
        $this->assertMongoDatabaseHas('users', [
            'email' => $user->email,
            'email_subscription_status' => true,
            'email_subscription_topics' => ['news', 'clubs', 'community'],
            'source' => $user->source,
            'source_detail' => $user->source_detail,
        ]);
    }

    /**
     * Test adding a subscription topics to an existing user with no duplicates.
     *
     * @return void
     */
    public function testAddSubscriptionToExistingUserWithNoDuplicates()
    {
        // Create a user who already has a subscription topic
        $user = factory(User::class)->create([
            'email_subscription_topics' => ['news'],
        ]);

        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => $user->email,
            'email_subscription_topic' => 'news',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertOk();

        // The email_subscription_topics should have no duplicates
        $this->assertMongoDatabaseHas('users', [
            'email' => $user->email,
            'email_subscription_topics' => ['news'],
            'source' => $user->source,
            'source_detail' => $user->source_detail,
        ]);
    }

    /**
     * Test adding a subscription topic to a new user.
     *
     * @return void
     */
    public function testAddSubscriptionToNewUser()
    {
        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics@dosomething.org',
            'email_subscription_topic' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertCreated();

        // The user should be created with the given email_subscription_topics, source, and source_detail
        $this->assertMongoDatabaseHas('users', [
            'email' => 'topics@dosomething.org',
            'email_subscription_status' => true,
            'email_subscription_topics' => ['scholarships'],
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);
    }

    /**
     * Test adding multiple subscription topics to a new user.
     *
     * @return void
     */
    public function testAddMultipleSubscriptionsToNewUser()
    {
        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics@dosomething.org',
            'email_subscription_topic' => ['news', 'clubs', 'community'],
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertCreated();

        // The user should be created with the given email_subscription_topics, source, and source_detail
        $this->assertMongoDatabaseHas('users', [
            'email' => 'topics@dosomething.org',
            'email_subscription_status' => true,
            'email_subscription_topics' => ['news', 'clubs', 'community'],
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);
    }

    /**
     * Test that a new user gets a password reset email.
     *
     * @return void
     */
    public function testNewUserGetsActivateAccountEmail()
    {
        $response = $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics@dosomething.org',
            'email_subscription_topic' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $response->assertCreated();

        $this->customerIoMock->shouldHaveReceived('trackEvent')->once();
        $this->assertMongoDatabaseHas('password_resets', [
            'email' => 'topics@dosomething.org',
        ]);
    }

    /**
     * Test rate limiting (10 posts per hour).
     *
     * @return void
     */
    public function testSubscriptionsThrottle()
    {
        // Post to /subscriptions 10 times
        for ($i = 0; $i < 10; $i++) {
            $response = $this->json('POST', 'v2/subscriptions', [
                'email' => 'topics' . $i . '@dosomething.org',
                'email_subscription_topic' => 'news',
                'source' => 'phoenix-next',
                'source_detail' => 'test_source_detail',
            ]);

            $response->assertCreated();
        }

        // Get a "Too Many Attempts" response when trying for an 11th post within an hour
        $lastResponse = $this->json('POST', 'v2/subscriptions', [
            'email' => 'topics11@dosomething.org',
            'email_subscription_topics' => 'scholarships',
            'source' => 'phoenix-next',
            'source_detail' => 'test_source_detail',
        ]);

        $lastResponse->assertStatus(429);
    }
}
