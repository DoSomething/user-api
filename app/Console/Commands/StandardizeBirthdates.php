<?php

namespace Northstar\Console\Commands;

use Carbon\Carbon;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

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
                $value = $user->getOriginal('birthdate');

                // If possible, switch the birthday to a date type, otherwise, wipe it!
                try {
                    $date = Carbon::parse($value);
                } catch (\Exception $e) {
                    $date = null;
                }

                $user->setBirthdateAttribute($date);
                $user->save();

                if (!$date) {
                    info('northstar:bday - removed invalid birthdate from '.$user->id.' - '.$value);
                } else {
                    info('northstar:bday - updated user '.$user->id.' birthdate from '.$value.' to '.$date);
                }
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        info('northstar:bday - All done!');
    }
}
