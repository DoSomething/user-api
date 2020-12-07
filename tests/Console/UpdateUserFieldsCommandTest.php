<?php

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class UpdateUserFieldsCommandTest extends BrowserKitTestCase
{
    /** @test */
    public function it_should_update_given_fields()
    {
        // Create the users given in the test csv
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e2']);
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e3']);
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e4']);
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e5']);

        // Run the user update command.
        Artisan::call('northstar:update', [
            'path' => 'tests/Console/files/example-user-updates.csv',
            'fields' => ['source', 'created_at'],
        ]);

        // Make sure the updates were made
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e2',
            'source' => 'source-1',
            'created_at' => Carbon::parse('2010-05-21 18:32:39'),
        ]);

        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e3',
            'source' => 'source-2',
            'created_at' => Carbon::parse('2012-11-05 20:23:32'),
        ]);

        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e4',
            'source' => 'source-3',
            'created_at' => Carbon::parse('2015-08-19 04:15:49'),
        ]);

        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e5',
            'source' => 'source-4',
            'created_at' => Carbon::parse('2018-01-01 10:00:00'),
        ]);
    }

    /** @test */
    public function it_should_update_email_topics()
    {
        // Create the users given in the test csv
        factory(User::class)->create([
            '_id' => '5acfbf609a89201c340543e2',
            'email_subscription_topics' => [],
        ]);
        factory(User::class)->create([
            '_id' => '5acfbf609a89201c340543e3',
            'email_subscription_topics' => ['news', 'lifestyle'],
        ]);
        factory(User::class)->create([
            '_id' => '5acfbf609a89201c340543e4',
            'email_subscription_topics' => ['lifestyle'],
        ]);
        $user = factory(User::class)->create([
            '_id' => '5acfbf609a89201c340543e5',
            'email_subscription_topics' => ['lifestyle'],
        ]);

        // Run the user update command.
        Artisan::call('northstar:update', [
            'path' => 'tests/Console/files/example-topic-updates.csv',
            'fields' => ['email_subscription_topics'],
        ]);

        // Updating user with no email topics
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e2',
            'email_subscription_topics' => ['lifestyle'],
        ]);

        // Updating user with 2 existing email topics
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e3',
            'email_subscription_topics' => [
                'news',
                'lifestyle',
                'scholarships',
            ],
        ]);

        // Updating user to make sure a topic isn't duplicated
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e4',
            'email_subscription_topics' => ['lifestyle'],
        ]);

        // Updating user with 1 existing email topic
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e5',
            'email_subscription_topics' => ['lifestyle', 'news'],
        ]);
    }

    /** @test */
    public function it_should_update_email_subscription_status()
    {
        // Create the users given in the test csv
        factory(User::class)->create([
            '_id' => '5f3dc976ea73310d6443dfe2',
            'email_subscription_status' => true,
            'email_subscription_topics' => ['community'],
        ]);
        factory(User::class)->create([
            '_id' => '5f3dc97dea73310d6443e002',
            'email_subscription_topics' => false,
            'email_subscription_topics' => [],
        ]);
        factory(User::class)->create([
            '_id' => '5f3dc97cea73310d6443dff9',
            'email_subscription_status' => true,
            'email_subscription_topics' => ['lifestyle'],
        ]);

        // Run the user update command.
        Artisan::call('northstar:update', [
            'path' => 'tests/Console/files/example-email-subscription-status-updates.csv',
            'fields' => ['email_subscription_status'],
        ]);

        // Verify users who have been unsubscribed
        $this->seeInDatabase('users', [
            '_id' => '5f3dc976ea73310d6443dfe2',
            'email_subscription_status' => false,
            'email_subscription_topics' => null,
        ]);
        $this->seeInDatabase('users', [
            '_id' => '5f3dc97cea73310d6443dff9',
            'email_subscription_status' => false,
            'email_subscription_topics' => null,
        ]);

        // Verify user who should be subscribed
        $this->seeInDatabase('users', [
            '_id' => '5f3dc97dea73310d6443e002',
            'email_subscription_status' => true,
            'email_subscription_topics' => null,
        ]);
    }
}
