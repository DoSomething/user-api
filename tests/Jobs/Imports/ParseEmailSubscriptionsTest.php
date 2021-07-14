<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\ImportEmailSubscriptions;
use App\Jobs\Imports\ParseEmailSubscriptions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParseEmailSubscriptionsTest extends TestCase
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
        $csv = file_get_contents('tests/Jobs/Imports/example-email-subs.csv');
        Storage::put('subscriptions.csv', $csv);

        $this->forceDispatch(
            new ParseEmailSubscriptions('subscriptions.csv', [
                'email_subscription_topic' => 'news',
                'source_detail' => 'phpunit',
            ]),
        );

        // There are 3 rows in the example CSV, so we should dispatch 3 jobs!
        Bus::assertDispatchedTimes(ImportEmailSubscriptions::class, 3);

        // Then, we should clean up after ourselves.
        Storage::assertMissing('subscriptions.csv');

        $this->assertMysqlDatabaseHas('import_files', [
            'import_type' => 'email-subscription',
            'filepath' => 'subscriptions.csv',
            'row_count' => 3,
        ]);
    }
}
