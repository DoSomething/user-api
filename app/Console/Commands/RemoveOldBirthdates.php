<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use MongoDB\BSON\UTCDateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class RemoveOldBirthdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:olds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove birthdates that are way too old.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get any users where 'birthdate' is before 1900... a.k.a way too old:
        $query = User::whereRaw([
            'birthdate' => [
                '$lt' => new UTCDateTime(strtotime('1900-01-01 00:00:00')),
            ],
        ]);

        $progressBar = $this->output->createProgressBar($query->count());
        $query->chunkById(200, function (Collection $users) use ($progressBar) {
            foreach ($users as $user) {
                $birthdate = $user->birthdate;
                $user->birthdate = null;
                $user->save();

                $progressBar->advance();

                info('Removed invalid birthdate.', [
                    'id' => $user->id,
                    'birthdate' => $birthdate,
                ]);
            }
        });

        $progressBar->finish();
    }
}
