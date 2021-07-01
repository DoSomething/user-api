<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\ImportMutePromotions;
use App\Jobs\Imports\ParseMutePromotions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParseMutePromotionsTest extends TestCase
{
    /**
     * Test that this job can parse an uploaded CSV.
     *
     * @return void
     */
    public function testParsesFile()
    {
        Bus::fake();
        Storage::fake();

        // Create a faked record that we can safely read & delete:
        $csv = file_get_contents('tests/Jobs/Imports/example-mute.csv');
        Storage::put('mute-promotions.csv', $csv);

        $this->forceDispatch(new ParseMutePromotions('mute-promotions.csv'));

        // There are 6 rows in the example CSV, so we should dispatch 6 jobs!
        Bus::assertDispatchedTimes(ImportMutePromotions::class, 6);

        // Then, we should clean up after ourselves.
        Storage::assertMissing('mute-promotions.csv');

        $this->assertMysqlDatabaseHas('import_files', [
            'import_type' => 'mute-promotions',
            'filepath' => 'mute-promotions.csv',
            'row_count' => 6,
        ]);
    }
}
