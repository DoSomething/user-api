<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\ImportMutePromotions;
use App\Models\ImportFile;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class ImportMutePromotionsTest extends TestCase
{
    /**
     * Test that a specified user can have promotions muted and that an event
     * for mute promotions is added to the logs.
     *
     * @return void
     */
    public function testMutesPromotionsForUserAndLogsEvent()
    {
        $user = factory(User::class)->create();

        $importFile = factory(ImportFile::class)->create();

        ImportMutePromotions::dispatch(
            ['northstar_id' => $user->id],
            $importFile,
        );

        $this->assertMysqlDatabaseHas('mute_promotions_logs', [
            'import_file_id' => $importFile->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Test that an exception is thrown if a user is not found, and no event
     * for mute promotions is added to the logs.
     *
     * @retuns void
     */
    public function testDoesNotMutePromotionsOrLogEventIfUserNotFound()
    {
        $user = factory(User::class)->create();

        $importFile = factory(ImportFile::class)->create();

        $this->expectException(ModelNotFoundException::class);

        ImportMutePromotions::dispatch(
            ['northstar_id' => 'non_existent_id'],
            $importFile,
        );

        $this->assertDatabaseMissing(
            'mute_promotions_logs',
            [
                'import_file_id' => $importFile->id,
                'user_id' => $user->id,
            ],
            'mysql',
        );
    }
}
