<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\CreateRockTheVoteReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CreateRockTheVoteReportTest extends TestCase
{
    /**
     * Test that this job creates a report for the given interval.
     *
     * @return void
     */
    public function testCreatesReport()
    {
        Bus::fake();

        $this->rockTheVoteMock = $this->mock(\App\Services\RockTheVote::class);

        $this->rockTheVoteMock->shouldReceive('createReport')->andReturn([
            'status' => 'queued',
            'record_count' => null,
            'current_index' => null,
            'status_url' =>
                'https://register.rockthevote.com/api/v4/registrant_reports/17',
            'download_url' => null,
        ]);

        $since = new Carbon('2021-05-26 10:07:00');
        $before = new Carbon('2021-05-26 11:37:00');

        $this->forceDispatch(new CreateRockTheVoteReport($since, $before));

        $this->rockTheVoteMock->shouldHaveReceived('createReport')->once();

        $this->assertMysqlDatabaseHas('rock_the_vote_reports', [
            'id' => 17,
            'status' => 'queued',
            'since' => '2021-05-26 10:07:00',
            'before' => '2021-05-26 11:37:00',
        ]);
    }
}
