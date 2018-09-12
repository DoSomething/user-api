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
    protected $signature = 'northstar:update
                            {path : URL of the csv with the updated data}
                            {fields* : Which fields we should look for in the csv and update on the user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For each user in the csv, overwrites each given field with the new value given in the csv.';

    /**
     * The total number of records to process.
     *
     * @var int
     */
    protected $totalCount;

    /**
     * The number of records that have been processed so far.
     *
     * @var int
     */
    protected $currentCount;

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
        info('northstar:update: Loading in csv from '.$path);

        $temp = tempnam(sys_get_temp_dir(), 'command_csv');
        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load the user updates from the CSV
        $usersCsv = Reader::createFromPath($temp, 'r');
        $usersCsv->setHeaderOffset(0);
        $usersToUpdate = $usersCsv->getRecords();

        info('northstar:update: Updating '.count($usersCsv).' users...');
        $fieldsToUpdate = $this->argument('fields');

        $this->totalCount = count($usersCsv);
        $currentCount = 0;

        foreach ($usersToUpdate as $userToUpdate) {
            $user = User::find($userToUpdate['northstar_id']);

            if (! $user) {
                info('northstar:update: Oops! Could not find user: '.$userToUpdate['northstar_id']);

                $this->logPercent();

                continue;
            }

            foreach ($fieldsToUpdate as $field) {
                if (! empty($userToUpdate[$field])) {
                    $user->{$field} = $userToUpdate[$field];
                }
            }

            $user->save();

            if ($this->option('verbose')) {
                info('northstar:update: Updated user - '.$user->id);
                $mb = memory_get_peak_usage() / 1000000;
                info('northstar:update: '.$mb.' Mb used');
            }

            $this->logPercent();
        }

        info('northstar:update: Done updating users!');
    }

    /**
     * Increment the current count and log an update if we've processed a multiple of 1000 records.
     *
     * @return void
     */
    public function logPercent()
    {
        $this->currentCount++;
        if ($this->currentCount % 1000 === 0) {
            $percent = ($this->currentCount / $this->totalCount) * 100;
            info('northstar:update: '.$this->currentCount.'/'.$this->totalCount.' - '.$percent.'% done');
        }
    }
}
