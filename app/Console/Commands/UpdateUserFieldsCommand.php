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

        // Load the missing signups from the CSV
        $users_csv = Reader::createFromPath($temp, 'r');
        $users_csv->setHeaderOffset(0);
        $users_to_update = $users_csv->getRecords();

        $this->line('Updating users...');
        $bar = $this->output->createProgressBar(count($users_csv));
        $fieldsToUpdate = $this->argument('fields');

        foreach ($users_to_update as $user_to_update) {
            $user = User::find($user_to_update['northstar_id']);

            foreach ($fieldsToUpdate as $field) {
                $user->{$field} = $user_to_update[$field];
            }

            $user->save();
            $bar->advance();
        }

        $bar->finish();
    }
}
