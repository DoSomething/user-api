<?php

namespace Northstar\Console\Commands;

use League\Csv\Reader;
use Northstar\Models\User;
use Illuminate\Console\Command;

class UpdateUserFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:update {path} {fields*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For each user in the csv, overwrites each given field with the new value given in the csv.';

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
        // Make a local copy of the CSV
        $path = $this->argument('path');
        $this->line('Loading in csv from '.$path);

        $temp = tempnam('temp', 'command_csv');
        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load the user updates from the CSV
        $usersCsv = Reader::createFromPath($temp, 'r');
        $usersCsv->setHeaderOffset(0);
        $usersToUpdate = $usersCsv->getRecords();

        $this->line('Updating users...');
        $bar = $this->output->createProgressBar(count($usersCsv));
        $fieldsToUpdate = $this->argument('fields');

        foreach ($usersToUpdate as $userToUpdate) {
            $user = User::find($userToUpdate['northstar_id']);

            if (! $user) {
                $this->line('Oops! Could not find user: ' . $userToUpdate['northstar_id']);

                continue;
            }

            foreach ($fieldsToUpdate as $field) {
                if (! empty($userToUpdate[$field])) {
                    $user->{$field} = $userToUpdate[$field];
                }
            }

            $user->save();
            $bar->advance();
        }

        $bar->finish();
    }
}
