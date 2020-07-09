<?php

use Northstar\Models\User;

class SubscriptionUpdateTest extends BrowserKitTestCase
{
    /**
     * Test that a user can add email subscriptions.
     * POST /v2/users/:id/subscriptions/:topic
     *
     * @return void
     */
    public function testAddSubscription()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', ['news']);
        $this->assertEquals(['news'], $user->fresh()->email_subscription_topics);
    }

    /**
     * Test that a user cannot add a duplicate email subscription.
     * POST /v2/users/:id/subscriptions/:topic
     *
     * @return void
     */
    public function testAddExistingSubscription()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => ['news'],
        ]);

        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', ['news']);
        $this->assertEquals(['news'], $user->fresh()->email_subscription_topics);
    }

    /**
     * Test that a user can mark remove email subscriptions.
     * DELETE /v2/users/:id/subscriptions/:topic
     *
     * @return void
     */
    public function testRemoveSubscription()
    {
        $user = factory(User::class)->create([
            'email_subscription_topics' => ['news'],
        ]);

        $this->asUser($user, ['user', 'write'])->delete('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', []);
        $this->assertEquals([], $user->fresh()->email_subscription_topics);
    }

    /**
     * Test that a user can't edit another user's subscriptions.
     * POST /v2/users/:id/subscriptions/:topic
     *
     * @return void
     */
    public function testNormalUsersCantChangeOthersSubscriptions()
    {
        $villain = factory(User::class)->create();
        $user = factory(User::class)->create();

        $this->asUser($villain, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(403);
    }
}
