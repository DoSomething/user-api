<?php

use App\Jobs\Imports\CreateRockTheVoteReport;
use App\Models\RockTheVoteReport;
use Carbon\Carbon;

class CreateRockTheVoteReportTest extends TestCase
{
    /**
     * Test that this job creates a report for the given interval.
     *
     * @return void
     */
    public function testCreatesReport()
    {
        $this->rockTheVoteMock = $this->mock(\App\Services\RockTheVote::class);
        $this->rockTheVoteMock->shouldReceive('createReport')->andReturn([
            'report_id' => 17,
            'status' => 'queued',
            'record_count' => null,
            'current_index' => null,
            'status_url' =>
                'https://register.rockthevote.com/api/v4/registrant_reports/17',
            'download_url' => null,
        ]);

        $since = new Carbon('2021-05-26 10:07:00');
        $before = new Carbon('2021-05-26 11:37:00');

        CreateRockTheVoteReport::dispatch($since, $before);

        $this->rockTheVoteMock->shouldHaveReceived('createReport')->once();

        $this->assertMysqlDatabaseHas('rock_the_vote_reports', [
            'id' => 17,
            'status' => 'queued',
            'since' => '2021-05-26 10:07:00',
            'before' => '2021-05-26 11:37:00',
        ]);
    }
}
