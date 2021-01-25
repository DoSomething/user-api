<?php

namespace App\Console\Commands;

use App\Models\Action;
use DB;
use Illuminate\Console\Command;

class UpdatePostsWithActionIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:updatepostswithactionids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds action_ids to all posts created pre post metadata.';

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
        $this->line(
            'rogue:updatepostswithactionids: Staring script to add action_ids to posts!',
        );

        // Grab all of the actions in the actions table.
        $actions = Action::all();

        // For each action, find all of its posts and add the action_id to each of them.
        foreach ($actions as $action) {
            DB::table('posts')
                ->where('action', $action->name)
                ->where('campaign_id', $action->campaign_id)
                ->where('type', $action->post_type)
                ->update(['action_id' => $action->id]);

            $this->line(
                'rogue:updatepostswithactionids: Updated all posts action_ids that have action ' .
                    $action->id,
            );
        }

        // Tell everyone we're done!
        $this->line('rogue:updatepostswithactionids: ALL DONE!');
    }
}
