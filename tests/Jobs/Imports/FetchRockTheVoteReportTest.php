<?php

namespace Tests\Jobs\Imports;

use App\Jobs\Imports\FetchRockTheVoteReport;
use App\Jobs\Imports\ParseRockTheVoteReport;
use App\Models\RockTheVoteReport;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FetchRockTheVoteReportTest extends TestCase
{
    protected function mockRockTheVoteResponse($status, ?array $overrides = [])
    {
        return array_merge(
            [
                'status' => $status,
                'current_index' => 50,
                'record_count' => 100,
                'status_url' =>
                    'https://register.rockthevote.com/api/v4/registrant_reports/17',
                'download_url' => null,
            ],
            $overrides,
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        // For our tests, we always want this feature enabled.
        config(['import.rock_the_vote.retry_failed_reports' => 'true']);
    }

    /**
     * Test that the job is retriggered if report is still queued.
     *
     * @return void
     */
    public function testHandlesQueuedStatus()
    {
        Bus::fake();

        $report = factory(RockTheVoteReport::class)->create();

        $this->rockTheVoteMock
            ->shouldReceive('getReportStatusById')
            ->andReturn($this->mockRockTheVoteResponse('queued'));

        $this->forceDispatch(new FetchRockTheVoteReport($report));

        $this->assertMysqlDatabaseHas('rock_the_vote_reports', [
            'id' => $report->id,
            'status' => 'queued',
        ]);

        // We should re-run this job until the report is done building:
        Bus::assertDispatched(FetchRockTheVoteReport::class);
    }

    /**
     * Test that a job is dispatched to import this report again after 2 minutes if status building.
     *
     * @return void
     */
    public function testHandlesBuildingStatus()
    {
        Bus::fake();

        $report = factory(RockTheVoteReport::class)->create();

        $this->rockTheVoteMock
            ->shouldReceive('getReportStatusById')
            ->andReturn([
                'status' => 'building',
                'record_count' => 117,
                'current_index' => 3,
            ]);

        $this->forceDispatch(new FetchRockTheVoteReport($report));

        $this->assertMysqlDatabaseHas('rock_the_vote_reports', [
            'id' => $report->id,
            'status' => 'building',
            'row_count' => 117,
            'current_index' => 3,
        ]);

        // We should re-run this job until the report is done building:
        Bus::assertDispatched(FetchRockTheVoteReport::class);
    }

    /**
     * Test that a new report is requested upon first failure.
     *
     * @return void
     */
    public function testHandlesFirstFailedStatus()
    {
        Bus::fake();

        $report = factory(RockTheVoteReport::class)->create();

        $this->rockTheVoteMock
            ->shouldReceive('getReportStatusById')
            ->andReturn($this->mockRockTheVoteResponse('failed'));

        $this->rockTheVoteMock->shouldReceive('createReport')->andReturn(
            $this->mockRockTheVoteResponse('queued', [
                'status_url' =>
                    'https://register.rockthevote.com/api/v4/registrant_reports/18',
            ]),
        );

        $this->forceDispatch(new FetchRockTheVoteReport($report));

        $this->assertMysqlDatabaseHas('rock_the_vote_reports', [
            'id' => $report->id,
            'status' => 'failed',
            'retry_report_id' => 18,
        ]);

        Bus::assertDispatched(FetchRockTheVoteReport::class);
    }

    /**
     * Test that a new report is *not* requested upon second failure.
     *
     * @return void
     */
    public function testHandlesSecondFailedStatus()
    {
        Bus::fake();

        $report = factory(RockTheVoteReport::class)->create([
            'retry_report_id' => 27,
        ]);

        $this->rockTheVoteMock
            ->shouldReceive('getReportStatusById')
            ->andReturn([
                'status' => 'failed',
                'record_count' => 0,
                'current_index' => 0,
            ]);

        $this->forceDispatch(new FetchRockTheVoteReport($report));

        Bus::assertNotDispatched(FetchRockTheVoteReport::class);
    }

    /**
     * Test that report is downloaded and its contents are dispatched for import if status complete.
     *
     * @return void
     */
    public function testHandlesCompleteStatus()
    {
        Bus::fake();
        Storage::fake();

        // Freeze the clock so we can assert expected filename below.
        $this->mockTime('06/03/2021 1:38:00pm');

        $report = factory(RockTheVoteReport::class)->create([
            'id' => 1234,
        ]);

        $this->rockTheVoteMock
            ->shouldReceive('getReportStatusById')
            ->andReturn([
                'status' => 'complete',
                'download_url' =>
                    'https://register.rockthevote.com/api/v4/registrant_reports/17/download',
                'record_count' => 1112,
                'current_index' => 1112,
            ]);

        $this->rockTheVoteMock->shouldReceive('getReportByUrl');

        $this->forceDispatch(new FetchRockTheVoteReport($report));

        $this->assertMysqlDatabaseHas('rock_the_vote_reports', [
            'id' => $report->id,
            'status' => 'complete',
        ]);

        Bus::assertDispatched(ParseRockTheVoteReport::class);
        Storage::assertExists('temporary/rock-the-vote-1234-1622727480.csv');
    }
}
