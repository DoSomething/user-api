<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Northstar\Services\CustomerIo;
use Northstar\Models\RefreshToken;

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
    public function handle(CustomerIo $customerIo)
    {
        $offset = $this->option('offset');

        $query = User::where('deletion_requested_at', '<', new Carbon($offset.'ago'));

        info('Anonymizing '.$query->count().' users...');

        $query->chunkById(200, function (Collection $users) use ($customerIo) {
            $users->each(function (User $user) use ($customerIo) {
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
                // TODO: This should be done via the soft deletion trait!
                $user->deleted_at = now();
                $user->saveQuietly();

                // Delete refresh tokens to end any active sessions:
                $token = RefreshToken::where('user_id', $user->id)->delete();

                // And finally, delete the user's profile in Customer.io:
                $customerIo->deleteUser($user);

                info('Deleted: '.$user->id);
            });
        });

        info('Done!');
    }
}
