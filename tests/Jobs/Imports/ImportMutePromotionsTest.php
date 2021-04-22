<?php

use App\Jobs\Imports\ImportMutePromotions;
use App\Models\ImportFile;
use App\Models\User;

class ImportMutePromotionsTest extends TestCase
{
    public function testExecutesMutesPromotionsRequest()
    {
        $user = factory(User::class)->create();

        $importFile = factory(ImportFile::class)->create();

        $job = new ImportMutePromotions(
            ['northstar_id' => $user->id],
            $importFile,
        );

        $job->handle();

        $this->assertMysqlDatabaseHas('mute_promotions_logs', [
            'import_file_id' => $importFile->id,
            'user_id' => $user->id,
        ]);
    }
}
