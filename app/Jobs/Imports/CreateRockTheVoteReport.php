<?php

namespace App\Jobs\Imports;

use App\Models\RockTheVoteReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class CreateRockTheVoteReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * The 'since' parameter to create a Rock The Vote report with.
     *
     * @var \Carbon\CarbonInterface
     */
    protected $since;

    /**
     * The 'before' parameter to create a Rock The Vote report with.
     *
     * @var \Carbon\CarbonInterface
     */
    protected $before;

    /**
     * The Rock The Vote report created upon success.
     *
     * @var DateTime
     */
    protected $report;

    /**
     * Create a new job instance.
     *
     * @param string $since
     * @param string $before
     * @return void
     */
    public function __construct($since, $before)
    {
        $this->since = $since;
        $this->before = $before;
    }

    /**
     * Execute the job to create a Rock The Vote report and import it after creation.
     *
     * @return array
     */
    public function handle()
    {
        info('Creating report', [
            'since' => $this->since,
            'before' => $this->before,
        ]);

        $this->report = RockTheVoteReport::createViaApi(
            $this->since,
            $this->before,
        );

        // TODO: We still need to make this job!! :)
        // ImportRockTheVoteReport::dispatch(null, $this->report);
    }

    /**
     * Returns the parameters passed to this job.
     *
     * @return array
     */
    public function getParameters()
    {
        return [
            'since' => $this->since,
            'before' => $this->before,
            'report' => $this->report,
        ];
    }
}
