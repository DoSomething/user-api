<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\ImportRockTheVoteRecord;
use App\Jobs\Imports\ParseRockTheVoteReport;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParseRockTheVoteReportTest extends TestCase
{
    /**
     * Test that this job can parse a Rock The Vote CSV report.
     *
     * @return void
     */
    public function testParsesReport()
    {
        Bus::fake();
        Storage::fake();

        // Create a fake record that we can safely read & delete:
        $csv = file_get_contents('tests/Jobs/Imports/example-rtv-report.csv');
        Storage::put('report.csv', $csv);

        $this->forceDispatch(new ParseRockTheVoteReport('report.csv'));

        // There are 4 rows in the example CSV, so we should dispatch 4 jobs!
        Bus::assertDispatchedTimes(ImportRockTheVoteRecord::class, 4);

        // Then, we should clean up after ourselves.
        Storage::assertMissing('report.csv');

        $this->assertMysqlDatabaseHas('import_files', [
            'import_type' => 'rock-the-vote',
            'filepath' => 'report.csv',
            'row_count' => 4,
        ]);
    }
}
