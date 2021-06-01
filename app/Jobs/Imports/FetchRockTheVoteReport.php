<?php

namespace App\Jobs\Imports;

use App\Jobs\Job;
use App\Models\RockTheVoteReport;
use App\Models\User;
use App\Services\RockTheVote;
use App\Types\ImportType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FetchRockTheVoteReport extends Job
{
    /**
     * The user who triggered this job, if triggered via web form.
     *
     * @var User
     */
    protected $user;

    /**
     * The report to download and import.
     *
     * @var RockTheVoteReport
     */
    protected $report;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param RockTheVoteReport $report
     * @return void
     */
    public function __construct(RockTheVoteReport $report, User $user = null)
    {
        $this->report = $report;
        $this->user = $user;
    }

    /**
     * Execute the job to check report status and download and import if complete.
     *
     * @return void
     */
    public function handle(RockTheVote $rockTheVote)
    {
        $now = Carbon::now();
        $reportId = $this->report->id;

        info("Checking status of report $reportId");

        $response = $rockTheVote->getReportStatusById($reportId);
        $status = $response['status'];

        $this->report->status = $status;
        $this->report->row_count = $response['record_count'];
        $this->report->current_index = $response['current_index'];

        info("Report $reportId status is $status");

        // If the report isn't yet complete, detour...
        if ($status !== 'complete') {
            return $this->handleIncompleteReport($status);
        }

        // ...otherwise, download it & hand off for processing!
        $path = "temporary/rock-the-vote-$reportId-{$now->timestamp}.csv";

        $contents = $rockTheVote->getReportByUrl($response['download_url']);
        Storage::put($path, $contents);

        info("Downloaded report $reportId");

        // TODO: Why is this delayed?
        ParseRockTheVoteReport::dispatch($this->report, $path)->delay(
            now()->addSeconds(3),
        );

        $this->report->dispatched_at = $now;
        $this->report->save();
    }

    /**
     * Handle cases where report is not yet finished building.
     *
     * @param string $status
     * @return void
     */
    public function handleIncompleteReport(string $status)
    {
        $reportId = $this->report->id;
        $this->report->save();

        // If we haven't failed, we're likely still building & should retry in a bit:
        if ($status !== 'failed') {
            self::dispatch($this->report, $this->user)->delay(
                now()->addMinutes(2),
            );

            return;
        }

        if (config('import.rock_the_vote.retry_failed_reports') !== 'true') {
            return;
        }

        // If failed and we've already retried this report, log the oddity and discard this job.
        if ($this->report->retry_report_id) {
            info(
                "Report $reportId already has retry report {$this->report->retry_report_id}",
            );

            return;
        }

        $retryReport = $this->report->createRetryReport();

        info(
            "Report $reportId created for retry report {$this->report->retry_report_id}",
        );

        self::dispatch($retryReport, $this->user);
    }

    /**
     * Returns the parameters passed to this job.
     *
     * @return array
     */
    public function getParameters()
    {
        return [
            'report' => $this->report,
            'user' => $this->user,
        ];
    }
}
