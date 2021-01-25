<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Post;
use Illuminate\Console\Command;

class RecountPostsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:recount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh post counts for all campaigns.';

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
        $pendingCounts = (new Post())
            ->newModelQuery()
            ->selectRaw('campaign_id, count(*) as count')
            ->whereReviewable()
            ->where('status', 'pending')
            ->groupBy('campaign_id')
            ->get();

        foreach ($pendingCounts as $pending) {
            $campaign = Campaign::find($pending->campaign_id);
            if ($campaign) {
                $campaign->pending_count = $pending->count;
                $campaign->save();
            }
        }

        $acceptedCounts = (new Post())
            ->newModelQuery()
            ->selectRaw('campaign_id, count(*) as count')
            ->whereReviewable()
            ->where('status', 'accepted')
            ->groupBy('campaign_id')
            ->get();

        foreach ($acceptedCounts as $accepted) {
            $campaign = Campaign::find($accepted->campaign_id);
            if ($campaign) {
                $campaign->accepted_count = $accepted->count;
                $campaign->save();
            }
        }
    }
}
