<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Northstar\Models\User;
use Illuminate\Console\Command;
use Northstar\Models\RefreshToken;

class AnonymizeUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:anon {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes PII for users given in CSV.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fields_to_unset = ['last_name', 'email', 'mobile', 'addr_street1', 'addr_street2', 'mobilecommons_id', 'drupal_id', 'facebook_id'];

        // Make a local copy of the CSV
        $path = $this->argument('path');
        $this->line('Loading in csv from '.$path);

        $temp = tempnam('temp', 'command_csv');
        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load the missing signups from the CSV
        $users_csv = Reader::createFromPath($temp, 'r');
        $users_csv->setHeaderOffset(0);
        $users = $users_csv->getRecords();

        $this->line('Anonymizing users...');
        $bar = $this->output->createProgressBar(count($users_csv));

        foreach ($users as $user) {
            $user = User::find($user['user_id']);

            // Overwrites
            $user->first_name = 'EU Member. Removed because of GDPR';
            $user->birthdate = $user->birthdate->year.'-01-01';

            // Removals
            foreach ($fields_to_unset as $field) {
                $user->unset($field);
            }
            $user->save();

            // Delete refresh token
            $token = RefreshToken::where('user_id', $user->id)->delete();

            $bar->advance();
        }

        $bar->finish();
    }
}
