<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Post;
use App\Models\Signup;
use DB;
use Illuminate\Console\Command;

class UpdateSignupAndPostCampaignIds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:updatesignupandpostcampaignids {--campaign= : The campaign_id to update signup and posts under}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates signup and post campaign ids after legacy campaign migration and killing of campaign runs.';

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
            'rogue:updatesignupandpostcampaignids: Starting script to update campaign ids!',
        );

        // Grab all of the campaigns in the campaigns table.
        $query = (new Campaign())->newQuery();

        if ($this->option('campaign')) {
            $query = $query->where('id', $this->option('campaign'));
        }

        $campaigns = $query->get();

        // Update each signup and the signup's post(s) in each campaign with new campaign id.
        foreach ($campaigns as $campaign) {
            $this->line(
                'rogue:updatesignupandpostcampaignids: Updating signups/posts under campaign id: ' .
                    $campaign->id,
            );

            // Update the all the signups' campaign_id under this campaign to the new $campaign->id
            DB::table('signups')
                ->where('campaign_run_id', $campaign->campaign_run_id)
                ->update(['campaign_id' => $campaign->id]);

            // Update all the posts' campaign_id under this campaign tot eh new $campaign->id
            DB::table('posts')
                ->join('signups', 'signups.id', '=', 'posts.signup_id')
                ->where('signups.campaign_run_id', $campaign->campaign_run_id)
                ->update(['posts.campaign_id' => $campaign->id]);
        }

        // Tell everyone we're done!
        $this->line('rogue:updatesignupandpostcampaignids: ALL DONE!');
    }
}
