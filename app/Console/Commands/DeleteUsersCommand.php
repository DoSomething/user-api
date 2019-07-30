<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Northstar\Services\CustomerIo;
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
    public function handle(CustomerIo $customerIo)
    {
        $input = file_get_contents($this->argument('input'));
        $csv = Reader::createFromString($input);
        $csv->setHeaderOffset(0);

        $this->line('Anonymizing '.count($csv).' users...');

        foreach ($csv->getRecords() as $record) {
            $id = $record[$this->option('id_column')];
            $user = User::find($id);

            if (! $user) {
                $this->warn('Skipping: '.$id);
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
            $user->saveQuietly();

            // Delete refresh tokens to end any active sessions:
            $token = RefreshToken::where('user_id', $user->id)->delete();

            // And finally, delete the user's profile in Customer.io:
            $customerIo->deleteUser($user);

            $this->info('Deleted: '.$user->id);
        }

        $this->info('Done!');
    }
}
