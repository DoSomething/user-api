<?php

namespace App\Console\Commands;

use App\Models\Action;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportPrePostMetaDataActions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:prepostmetadataactionsimport {path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports backfill of actions for all posts pre post metadata in the given (link to a) CSV file.';

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
        $this->line(
            'rogue:prepostmetadataactionsimport: Loading in csv from ' . $path,
        );

        $temp = tempnam(sys_get_temp_dir(), 'command_csv');
        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load the actions from the CSV
        $backfill_actions_csv = Reader::createFromPath($temp, 'r');
        $backfill_actions_csv->setHeaderOffset(0);
        $backfill_actions = iterator_to_array(
            $backfill_actions_csv->getRecords(),
        );

        // Import each backfill action
        $this->line(
            'rogue:prepostmetadataactionsimport: Loading in csv from ' . $path,
        );

        foreach ($backfill_actions as $backfill_action) {
            // See if the action exists
            $existing_action = Action::where(
                'campaign_id',
                $backfill_action['campaign_id'],
            )
                ->where('name', $backfill_action['action'])
                ->where('post_type', $backfill_action['type'])
                ->first();

            // Create the action if there isn't one already
            if (!$existing_action) {
                $action = Action::create([
                    'name' => $backfill_action['action'],
                    'campaign_id' => $backfill_action['campaign_id'],
                    'post_type' => $backfill_action['type'],
                    'reportback' => $backfill_action['reportback'],
                    'civic_action' => $backfill_action['civic_action'],
                    'scholarship_entry' =>
                        $backfill_action['scholarship_entry'],
                    'noun' => $backfill_action['noun'],
                    'verb' => $backfill_action['verb'],
                ]);

                $this->line(
                    'rogue:prepostmetadataactionsimport: Created action ' .
                        $action->id,
                );
            }
        }

        // Tell everyone we're done!
        $this->line('rogue:prepostmetadataactionsimport: ALL DONE!');
    }
}
