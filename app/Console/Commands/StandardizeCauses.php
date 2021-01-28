<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;

class StandardizeCauses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:standardizecauses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates causes on campaigns to be lowercased and hyphenated';

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
        $this->info(
            'rogue:standardizecauses: Starting to clean up the causes!',
        );

        // Grab every campaign that has a cause set
        $campaignsWithCause = Campaign::whereNotNull('cause')->get();

        foreach ($campaignsWithCause as $campaign) {
            $this->info('Updating campaign ' . $campaign->id);

            // Grab the old causes
            $oldCauses = $campaign->cause;
            $this->info('--From:' . implode(',', $oldCauses));

            // Trim whitespace, make the causes lowercase, and replaces spaces with hyphens
            // Add cleaned up causes to a new array
            $newCauses = [];
            foreach ($oldCauses as $oldCause) {
                array_push(
                    $newCauses,
                    str_replace(' ', '-', strtolower(trim($oldCause))),
                );
            }
            $this->info('--To:' . implode(',', $newCauses));

            // Set and save the clean causes
            $campaign->cause = $newCauses;
            $campaign->save();

            $this->info('--✔ Successfully updated!');
        }

        $this->info('rogue:standardizecauses: All causes cleaned up!');
    }
}
