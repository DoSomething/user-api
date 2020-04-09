<?php

use Carbon\Carbon;
use Northstar\Models\User;
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
        Artisan::call('northstar:update', ['path' => 'tests/Console/example-user-updates.csv', 'fields' => ['source', 'created_at']]);

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
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e2', 'email_subscription_topics' => []]);
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e3', 'email_subscription_topics' => ['news', 'lifestyle']]);
        factory(User::class)->create(['_id' => '5acfbf609a89201c340543e4', 'email_subscription_topics' => ['lifestyle']]);
        $user = factory(User::class)->create(['_id' => '5acfbf609a89201c340543e5', 'email_subscription_topics' => ['lifestyle']]);

        // Run the user update command.
        Artisan::call('northstar:update', ['path' => 'tests/Console/example-topic-updates.csv', 'fields' => ['email_subscription_topics']]);

        // Updating user with no email topics
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e2',
            'email_subscription_topics' => ['lifestyle'],
        ]);

        // Updating user with 2 existing email topics
        $this->seeInDatabase('users', [
            '_id' => '5acfbf609a89201c340543e3',
            'email_subscription_topics' => ['news', 'lifestyle', 'scholarships'],
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
}
