<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Northstar\Models\User;
use Illuminate\Console\Command;

class DeleteUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:delete {input=php://stdin} {--id_column=id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue users for deletion, given a CSV of IDs.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $input = file_get_contents($this->argument('input'));
        $csv = Reader::createFromString($input);
        $csv->setHeaderOffset(0);

        info('Queueing '.count($csv).' users for deletion...');

        foreach ($csv->getRecords() as $record) {
            $id = $record[$this->option('id_column')];
            $user = User::find($id);

            if (! $user) {
                info('Skipping: '.$id);
                continue;
            }

            $user->deletion_requested_at = now();
            $user->save();
        }

        info('Done!');
    }
}
