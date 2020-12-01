<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use League\Csv\Reader;

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
    protected $description = 'Delete users from our systems, given a CSV of IDs.';

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

        info('Immediately deleting ' . count($csv) . ' users...');

        foreach ($csv->getRecords() as $record) {
            $id = $record[$this->option('id_column')];
            $user = User::find($id);

            if (!$user) {
                info('Skipping: ' . $id);
                continue;
            }

            $user->delete();
        }

        info('Done!');
    }
}
