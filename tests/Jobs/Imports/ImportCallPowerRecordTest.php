<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\ImportCallPowerRecord;
use App\Models\Action;
use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class ImportCallPowerRecordTest extends TestCase
{
    /**
     * Test that a post with a completed status successfully saves.
     *
     * @return void
     */
    public function testCompletedCallStatus()
    {
        $action = factory(Action::class)
            ->state('callpower')
            ->create();

        $parameters = [
            'mobile' => '2224567891',
            'callpower_campaign_id' => $action->callpower_campaign_id,
            'status' => 'completed',
            'call_timestamp' => '2017-11-09 06:34:01.185035',
            'call_duration' => 50,
            'campaign_target_name' => 'Mickey Mouse',
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
            'callpower_campaign_name' => 'Test',
            'number_dialed_into' => '+12028519273',
        ];

        ImportCallPowerRecord::dispatch($parameters);

        $this->assertMongoDatabaseHas('users', [
            'mobile' => '+12224567891',
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'action_id' => $action->id,
            'status' => 'accepted',
            'source' => 'importer-client',
            'source_details' => 'CallPower',
        ]);
    }

    /**
     * Test that a post with a busy status successfully saves.
     *
     * @return void
     */
    public function testBusyCallStatus()
    {
        $user = factory(User::class)->create();

        $action = factory(Action::class)
            ->state('callpower')
            ->create();

        // Create a post with completed as the status.
        $parameters = [
            'mobile' => $user->mobile,
            'callpower_campaign_id' => $action->callpower_campaign_id,
            'status' => 'busy',
            'call_timestamp' => '2017-11-09 06:34:01.185035',
            'call_duration' => 50,
            'campaign_target_name' => 'Mickey Mouse',
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
            'callpower_campaign_name' => 'Test',
            'number_dialed_into' => '+12028519273',
        ];

        ImportCallPowerRecord::dispatch($parameters);

        $this->assertMysqlDatabaseHas('posts', [
            'action_id' => $action->id,
            'northstar_id' => $user->id,
            'status' => 'incomplete',
        ]);
    }

    /**
     * Test that a post with an invalid CallPower campaign id is not made.
     *
     * @return void
     */
    public function testFailedPostWithInvalidCallPowerCampaignId()
    {
        $user = factory(User::class)->create();

        $action = factory(Action::class)
            ->state('callpower')
            ->create([
                'callpower_campaign_id' => 1,
            ]);

        // Since we don't have an action for this CallPower ID, we should get an error:
        $this->expectExceptionMessage(
            'Could not find action with callpower_campaign_id',
        );

        ImportCallPowerRecord::dispatch([
            'mobile' => $user->mobile,
            'callpower_campaign_id' => 99,
            'status' => 'busy',
            'call_timestamp' => '2017-11-09 06:34:01.185035',
            'call_duration' => 50,
            'campaign_target_name' => 'Mickey Mouse',
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
            'callpower_campaign_name' => 'Test',
            'number_dialed_into' => '+12028519273',
        ]);

        $this->assertMysqlDatabaseMissing('posts', [
            'northstar_id' => $user->id,
        ]);
    }

    /**
     * Test an anonymous mobile number is not processed.
     *
     * @skip
     */
    public function testAnonymousMobile()
    {
        ImportCallPowerRecord::dispatch([
            'mobile' => '+266696687',
            'callpower_campaign_id' => 1,
            'status' => 'busy',
            'call_timestamp' => '2017-11-09 06:34:01.185035',
            'call_duration' => 50,
            'campaign_target_name' => 'Mickey Mouse',
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
            'callpower_campaign_name' => 'Test',
            'number_dialed_into' => '+12028519273',
        ]);

        $this->assertEquals(0, User::count());
        $this->assertEquals(0, Post::count());
    }
}
