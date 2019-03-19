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
        // Use low priority queue for these updates
        config(['queue.jobs.users' => 'low']);

        // Make a local copy of the CSV
        $path = $this->argument('path');
        $this->line('northstar:update: Loading in csv from '.$path);

        $temp = tempnam(sys_get_temp_dir(), 'command_csv');
        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load the user updates from the CSV
        $usersCsv = Reader::createFromPath($temp, 'r');
        $usersCsv->setHeaderOffset(0);
        $usersToUpdate = $usersCsv->getRecords();

        $this->line('northstar:update: Updating '.count($usersCsv).' users...');

        $fieldsToUpdate = $this->argument('fields');

        $this->totalCount = count($usersCsv);
        $currentCount = 0;

        $user = null;

        foreach ($usersToUpdate as $userToUpdate) {
            $user = User::find($userToUpdate['northstar_id']);

            if (! $user) {
                $this->line('northstar:update: Oops! Could not find user: '.$userToUpdate['northstar_id']);

                $this->logPercent();

                continue;
            }

            foreach ($fieldsToUpdate as $field) {
                if (! empty($userToUpdate[$field])) {
                    // Special instructions when working with array field
                    if ($field === 'email_subscription_topics') {
                        // Get current email topics
                        $topics = $user->email_subscription_topics ? $user->email_subscription_topics : [];

                        // Don't add topic if it is already there
                        if (in_array($userToUpdate[$field], $topics)) {
                            continue;
                        }

                        // Add the new topic to our array
                        array_push($topics, $userToUpdate[$field]);

                        // Add the full array of topics to the user
                        $user->email_subscription_topics = $topics;
                    } else {
                        $user->{$field} = $userToUpdate[$field];
                    }
                }
            }

            $user->save();

            if ($this->option('verbose')) {
                $this->line('northstar:update: Updated user - '.$user->id);
                $mb = memory_get_peak_usage() / 1000000;
                $this->line('northstar:update: '.$mb.' Mb used');
            }

            $this->logPercent();
            unset($userToUpdate);
        }

        $this->line('northstar:update: Done updating users!');
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
            $mb = memory_get_peak_usage() / 1000000;
            $this->line('northstar:update: '.$this->currentCount.'/'.$this->totalCount.' - '.$percent.'% done - '.$mb.' Mb used');
        }
    }
}
