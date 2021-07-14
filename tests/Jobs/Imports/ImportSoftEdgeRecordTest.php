<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\ImportSoftEdgeRecord;
use App\Models\Action;
use App\Models\User;
use Tests\TestCase;

class ImportSoftEdgeRecordTest extends TestCase
{
    /**
     * Test that a post with a completed status successfully saves.
     *
     * @return void
     */
    public function testImportSoftEdgeRecord()
    {
        $user = factory(User::class)->create();

        $action = factory(Action::class)
            ->state('softedge')
            ->create();

        ImportSoftEdgeRecord::dispatch([
            'action_id' => $action->id,
            'northstar_id' => $user->id,
            'email_timestamp' => '2017-11-07 18:54:10.829655',
            'campaign_target_name' => $this->faker->name,
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
        ]);

        $this->assertMysqlDatabaseHas('posts', [
            'type' => 'email',
            'action_id' => $action->id,
            'northstar_id' => $user->id,
            'status' => 'accepted',
            'source' => 'importer-client',
            'source_details' => 'SoftEdge',
        ]);
    }

    /**
     * Test that a post cannot be created for a nonexistant user.
     *
     * @return void
     */
    public function testFailsWithInvalidUser()
    {
        $action = factory(Action::class)
            ->state('softedge')
            ->create();

        $this->expectExceptionMessage(
            'No query results for model [App\Models\User]',
        );

        ImportSoftEdgeRecord::dispatch([
            'action_id' => $action->id,
            'northstar_id' => 'lold8adsad98a7d8998asd7a',
            'email_timestamp' => '2017-11-07 18:54:10.829655',
            'campaign_target_name' => $this->faker->name,
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
        ]);

        $this->assertMysqlDatabaseMissing('posts', [
            'northstar_id' => 'lold8adsad98a7d8998asd7a',
        ]);
    }

    /**
     * Test that a post for a non-email action won't save.
     *
     * @return void
     */
    public function testFailsWithIncompatibleActionType()
    {
        $user = factory(User::class)->create();

        $action = factory(Action::class)
            ->state('callpower') // <-- !!!
            ->create();

        $this->expectExceptionMessage(
            'Received SoftEdge import for non-email action.',
        );

        ImportSoftEdgeRecord::dispatch([
            'action_id' => $action->id,
            'northstar_id' => $user->id,
            'email_timestamp' => '2017-11-07 18:54:10.829655',
            'campaign_target_name' => $this->faker->name,
            'campaign_target_title' => 'Representative',
            'campaign_target_district' => 'FL-7',
        ]);

        $this->assertMysqlDatabaseMissing('posts', [
            'northstar_id' => $user->id,
        ]);
    }
}
