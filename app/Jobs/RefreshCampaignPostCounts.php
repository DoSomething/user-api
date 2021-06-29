<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\Post;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshCampaignPostCounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The campaign we're refreshing.
     *
     * @var Campaign
     */
    protected $campaign;

    /**
     * When was this job dispatched?
     *
     * @var \Carbon\Carbon
     */
    protected $dispatchedAt;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
        $this->dispatchedAt = now();

        $this->onQueue(config('queue.names.low'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // If we've already updated this campaign after this job was dispatched,
        // we can probably skip recounting to reduce unnecessary database load:
        if ($this->campaign->updated_at > $this->dispatchedAt) {
            return;
        }

        $accepted_count = Post::getPostCount($this->campaign, 'accepted');
        $pending_count = Post::getPostCount($this->campaign, 'pending');

        $this->campaign->pending_count = $pending_count;
        $this->campaign->accepted_count = $accepted_count;

        info('Recalculated post counts', [
            'campaign_id' => $this->campaign->id,
            'accepted_count' => $accepted_count,
            'pending_count' => $pending_count,
        ]);

        $this->campaign->save();
    }
}
