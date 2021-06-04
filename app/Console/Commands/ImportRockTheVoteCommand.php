<?php

namespace App\Console\Commands;

use App\Jobs\Imports\CreateRockTheVoteReport;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportRockTheVoteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:rock-the-vote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports Rock The Vote registrations from the past hour.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // We want all registrations in the past hour (including a 30 minute overlap to
        // ensure that we don't lose any records to a gap between scheduled jobs):
        $now = CarbonImmutable::now();
        $since = $now->subHours(1)->subMinutes(30);

        Log::debug('Executing import command', [
            'since' => $since,
            'before' => $now,
        ]);

        CreateRockTheVoteReport::dispatch($since, $now);
    }
}
