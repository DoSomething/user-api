<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SetCommunityTopic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:community';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add "community" to the email topics for each user who is subscribed.';

    /**
     * The number of users updated so far.
     *
     * @var string
     */
    protected $currentCount = 0;

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

        // Grab users who have email addresses
        $query = (new User)->newQuery();
        $query = $query->where('email_subscription_status', true);
        $query = $query->whereNotIn('email_subscription_topics', ['community']);

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->line('northstar:community - No users need updating!');

            return;
        }

        $query->chunkById(200, function (Collection $users) use ($totalCount) {
            $users->each(function (User $user) use ($totalCount) {
                // Add "community" for each user here
                $user->addEmailSubscriptionTopic('community');
                $user->save();

                $this->line('northstar:community - User '.$user->id.' given community topic');
            });

            // Logging to track progress (may read over 100% at the end when there are less than 200 users in the last chunk)
            $this->currentCount += 200;
            $percentDone = ($this->currentCount / $totalCount) * 100;
            $this->line('northstar:community - status update - '.$this->currentCount.'/'.$totalCount.' - '.$percentDone.'% done');
        });

        $this->line('northstar:community - Added "community" to each appropriate user!');
    }
}
