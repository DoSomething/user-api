<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\BrowserKitTestCase;

class CommunityTopicBackfillTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_not_assign_community_to_unsubscribed_users()
    {
        $user = factory(User::class)->create([
            'email_subscription_status' => false,
            'email_subscription_topics' => [],
        ]);

        // Run the community backfill command.
        Artisan::call('northstar:community');

        // Make sure no updates were made to this user
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'email_subscription_status' => false,
            'email_subscription_topics' => null,
        ]);
    }

    /** @test */
    public function it_should_not_duplicate_community_topic()
    {
        $user = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => ['community'],
        ]);

        // Run the community backfill command.
        Artisan::call('northstar:community');

        // Make sure no updates were made to this user
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'email_subscription_status' => true,
            'email_subscription_topics' => ['community'],
        ]);
    }

    /** @test */
    public function it_should_add_community_to_subscribed_users()
    {
        $user = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => [],
        ]);

        // Run the community backfill command.
        Artisan::call('northstar:community');

        // Make sure community was added to this user
        $this->seeInMongoDatabase('users', [
            '_id' => $user->id,
            'email_subscription_status' => true,
            'email_subscription_topics' => ['community'],
        ]);
    }

    /** @test */
    public function it_should_ignore_other_topics()
    {
        $user1 = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => ['news'],
        ]);
        $user2 = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => ['news', 'scholarships'],
        ]);
        $user3 = factory(User::class)->create([
            'email_subscription_status' => true,
            'email_subscription_topics' => [
                'news',
                'scholarships',
                'lifestyle',
            ],
        ]);

        // Run the community backfill command.
        Artisan::call('northstar:community');

        // Make sure the updates were made
        $this->seeInMongoDatabase('users', [
            '_id' => $user1->id,
            'email_subscription_status' => true,
            'email_subscription_topics' => ['news', 'community'],
        ]);

        $this->seeInMongoDatabase('users', [
            '_id' => $user2->id,
            'email_subscription_status' => true,
            'email_subscription_topics' => [
                'news',
                'scholarships',
                'community',
            ],
        ]);

        $this->seeInMongoDatabase('users', [
            '_id' => $user3->id,
            'email_subscription_status' => true,
            'email_subscription_topics' => [
                'news',
                'scholarships',
                'lifestyle',
                'community',
            ],
        ]);
    }
}
