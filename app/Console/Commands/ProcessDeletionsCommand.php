<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ProcessDeletionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:process-deletions {--offset=14 days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process users that have been queued for deletion.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $offset = $this->option('offset');

        $query = User::where('deletion_requested_at', '<', new Carbon($offset.'ago'));

        info('Anonymizing '.$query->count().' users...');

        $query->chunkById(200, function (Collection $users) {
            $users->each(function (User $user) {
                $user->delete();
            });
        });

        info('Done!');
    }
}
