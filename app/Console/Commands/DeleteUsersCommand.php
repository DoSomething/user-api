<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Northstar\Models\RefreshToken;

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
    protected $description = 'Delete (anonymize) users, given a CSV of IDs.';

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

        $this->line('Anonymized '.count($csv).' users...');

        foreach ($csv->getRecords() as $record) {
            $user = User::find($record[$this->option('id_column')]);
            if (! $user) {
                $this->warn('Skipping: '.$record[$this->option('id_column')]);
                continue;
            }

            // Anonymize birthdate so we can see demographics of deleted users:
            if ($user->birthdate) {
                $user->birthdate = $user->birthdate->year.'-01-01';
            }

            // Remove all fields except some non-identifiable demographics:
            $fields = array_keys(array_except($user->getAttributes(), [
                '_id', 'birthdate', 'language', 'source', 'source_detail',
                'addr_city', 'addr_state', 'addr_zip', 'country',
                'created_at', 'updated_at',
            ]));

            if ($fields) {
                $user->drop($fields);
            }

            // Set a flag so we know this user was deleted:
            $user->deleted_at = now();
            $user->save();

            // Delete refresh tokens to end any active sessions:
            $token = RefreshToken::where('user_id', $user->id)->delete();

            $this->info('Deleted: '.$user->id);
        }
    }
}
