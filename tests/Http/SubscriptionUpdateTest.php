<?php

use Carbon\Carbon;
use Northstar\Models\User;

class SubscriptionUpdateTest extends BrowserKitTestCase
{
  /**
     * Test that a user can mark add email subscriptions.
     * POST /v2/users/:id/subscriptions/:topic
     *
     * @return void
     */
    public function testAddSubscription()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', ["news"]);
    }

    public function testAddExistingSubscription()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');
        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', ["news"]);
    }

    /**
     * Test that a user can mark remove email subscriptions.
     * POST /v2/users/:id/subscriptions/:topic
     *
     * @return void
     */
    public function testRemoveSubscription()
    {
        $user = factory(User::class)->create();

        $this->asUser($user, ['user', 'write'])->post('v2/users/'.$user->id.'/subscriptions/news');
        $this->asUser($user, ['user', 'write'])->delete('v2/users/'.$user->id.'/subscriptions/news');

        $this->assertResponseStatus(200);
        $this->seeJsonField('data.email_subscription_topics', []);
    }


    /**
     * Test that a user can mark remove email subscriptions.
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