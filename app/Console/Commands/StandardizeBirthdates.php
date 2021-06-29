<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use MongoDB\BSON\UTCDateTime;

class StandardizeBirthdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:bday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invalid birthdates to be valid, if possible.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        info('northstar:bday - Starting up!');
        // Get users whose `birthdate` is not the correct type
        $query = User::whereRaw([
            'birthdate' => [
                '$exists' => true,
                '$not' => [
                    '$type' => 9,
                ],
            ],
        ]);
        $progressBar = $this->output->createProgressBar($query->count());

        $query->chunkById(200, function (Collection $users) use ($progressBar) {
            foreach ($users as $user) {
                // @Question: may not need to convert to Carbon date if using updated getOrigina().
                $value = $user->getRawOriginal('birthdate');

                // If possible, switch the birthday to a date type, otherwise, wipe it!
                try {
                    $date = Carbon::parse($value);
                    app('db')
                        ->connection('mongodb')
                        ->collection('users')
                        ->where(['_id' => $user->id])
                        ->update([
                            'birthdate' => new UTCDateTime(
                                $date->getTimestamp() * 1000,
                            ),
                        ]);
                } catch (\Exception $e) {
                    $user->setBirthdateAttribute(null);
                    $user->save();
                }

                if (!$user->fresh()->birthdate) {
                    info(
                        'northstar:bday - removed invalid birthdate from ' .
                            $user->id .
                            ' - ' .
                            $value,
                    );
                } else {
                    info(
                        'northstar:bday - updated user ' .
                            $user->id .
                            ' birthdate from ' .
                            $value .
                            ' to ' .
                            $date,
                    );
                }
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        info('northstar:bday - All done!');
    }
}
