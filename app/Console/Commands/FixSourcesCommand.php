<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use League\Csv\Reader;
use Northstar\Models\User;

class FixSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:sources {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix incorrect sources using the provided CSV.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = base_path($this->argument('path'));

        $reader = Reader::createFromPath($path);
        foreach ($reader->fetchAssoc(0) as $index => $row) {
            $user = User::findOrFail($row['field_northstar_id_value']);
            $originalCreatedAt = Carbon::createFromTimestamp($row['created']);

            $threshold = $originalCreatedAt->subMonth(1);

            // If the user was created more than a month before the Niche
            // import, then we'll assume they were correctly backfilled.
            if ($user->created_at->lt($threshold)) {
                $this->warn('Not updating source for '.$user->id.', created'.$user->created_at->toFormattedDateString());

                continue;
            }

            // Otherwise, reset their source to expected 'niche'.
            $user->source = $row['field_user_registration_source_value'];
            $user->source_detail = null;
            $user->save();

            $this->line('Updated source for '.$user->id.'.');
        }

        $this->info('Done!');
    }
}
