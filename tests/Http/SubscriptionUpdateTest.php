<?php

namespace Tests\Http;

use App\Models\User;
use Tests\BrowserKitTestCase;

class SubscriptionUpdateTest extends BrowserKitTestCase
{
    /**
     * Test that a user can add email subscriptions.
     *
     * @return void
     */
    public function testAddSubscription()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/subscriptions/news',
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', ['news']);
        $this->assertEquals(
            ['news'],
            $user->fresh()->email_subscription_topics,
        );
    }

    /**
     * Test that a user cannot add a duplicate email subscription.
     *
     * @return void
     */
    public function testAddExistingSubscription()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => ['news'],
        ]);

        $this->asUser($user, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/subscriptions/news',
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', ['news']);
        $this->assertEquals(
            ['news'],
            $user->fresh()->email_subscription_topics,
        );
    }

    /**
     * Test that a user cannot add an invalid email subscription.
     *
     * @return void
     */
    public function testCantAddInvalidSubscription()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/subscriptions/invalid',
        );

        // This route won't exist since the topic is invalid.
        $this->assertResponseStatus(404);
        $this->assertEquals([], $user->fresh()->email_subscription_topics);
    }

    /**
     * Test that a user can mark remove email subscriptions.
     *
     * @return void
     */
    public function testRemoveSubscription()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => ['news'],
        ]);

        $this->asUser($user, ['user', 'write'])->delete(
            'v2/users/' . $user->id . '/subscriptions/news',
        );

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', []);
        $this->assertEquals([], $user->fresh()->email_subscription_topics);
    }

    /**
     * Test that a user can't edit another user's subscriptions.
     *
     * @return void
     */
    public function testNormalUsersCantChangeOthersSubscriptions()
    {
        $villain = factory(User::class)->create();
        $user = factory(User::class)->create();

        $this->asUser($villain, ['user', 'write'])->post(
            'v2/users/' . $user->id . '/subscriptions/news',
        );

        $this->assertResponseStatus(403);
    }
}
