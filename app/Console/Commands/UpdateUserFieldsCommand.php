<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use League\Csv\Reader;

class UpdateUserFieldsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:update
                            {input=php://stdin}
                            {--field=* : Each field that should be updated}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For each user in the csv, overwrites each field with its new value.';

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
     * @return bool
     */
    public function handle()
    {
        // Use low priority queue for these updates
        config(['queue.jobs.users' => 'low']);

        $input = file_get_contents($this->argument('input'));
        $csv = Reader::createFromString($input);
        $csv->setHeaderOffset(0);

        $this->totalCount = count($csv);

        $this->line(
            'northstar:update: Updating ' . $this->totalCount . ' users...',
        );

        $fieldsToUpdate = $this->option('field');
        $currentCount = 0;
        $user = null;

        foreach ($csv->getRecords() as $userToUpdate) {
            $user = User::find($userToUpdate['northstar_id']);

            if (!$user) {
                $this->line(
                    'northstar:update: Oops! Could not find user: ' .
                        $userToUpdate['northstar_id'],
                );

                $this->logPercent();

                continue;
            }

            foreach ($fieldsToUpdate as $field) {
                $updateFieldValue = $userToUpdate[$field];

                if (empty($updateFieldValue)) {
                    continue;
                }

                // Special instructions when working with array field
                if ($field === 'email_subscription_topics') {
                    $user->addEmailSubscriptionTopic($updateFieldValue);
                    continue;
                }

                if ($field === 'email_subscription_status') {
                    $user->{$field} = filter_var(
                        $updateFieldValue,
                        FILTER_VALIDATE_BOOLEAN,
                    );
                    continue;
                }

                $user->{$field} = $updateFieldValue;
            }

            $user->save();

            if ($this->option('verbose')) {
                $this->line('northstar:update: Updated user - ' . $user->id);
                $this->line(
                    'northstar:update: ' . $this->getMbUsed() . ' Mb used',
                );
            }

            $this->logPercent();
            unset($userToUpdate);
        }

        $this->line('northstar:update: Done updating users!');

        return 0;
    }

    /**
     * Returns number of megabytes used.
     *
     * @return float
     */
    public function getMbUsed()
    {
        return memory_get_peak_usage() / 1000000;
    }

    /**
     * Increment the current count and log an update if we've processed a multiple of 1000 records.
     *
     * @return void
     */
    public function logPercent()
    {
        $this->currentCount++;

        if ($this->currentCount % 1000 !== 0) {
            return;
        }

        $this->line(
            'northstar:update: ' .
                $this->currentCount .
                '/' .
                $this->totalCount .
                ' - ' .
                ($this->currentCount / $this->totalCount) * 100 .
                '% done - ' .
                $this->getMbUsed() .
                ' Mb used',
        );
    }
}
