<?php

use Carbon\Carbon;
use Northstar\Models\User;

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


        // Run the Birthdate Standardizer command.
        $this->artisan('northstar:update', ['path' => 'tests/Console/example-user-updates.csv', 'fields' => ['source', 'created_at']]);

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
}
