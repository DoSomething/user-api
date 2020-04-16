<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SetDefaultSmsSubscriptionTopics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:default-sms-topics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add "general" and "voting" SMS topics for each user who is subscribed to SMS.';

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

        // Grab users who are subscribers and do not have topics set.
        $query = (new User)->newQuery();
        $query = $query->whereIn('sms_status', ['active', 'less', 'pending']);
        $query = $query->where('sms_subscription_topics', null);

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->line('northstar:default-sms-topics - No users need updating.');

            return;
        }

        $query->chunkById(200, function (Collection $users) use ($totalCount) {
            $users->each(function (User $user) use ($totalCount) {
                $user->sms_subscription_topics = ['general', 'voting'];
                $user->save();
            });

            // Logging to track progress (may read over 100% at the end when there are less than 200 users in the last chunk)
            $this->currentCount += 200;
            $percentDone = ($this->currentCount / $totalCount) * 100;
            $this->line('northstar:default-sms-topics - status update - '.$this->currentCount.'/'.$totalCount.' - '.$percentDone.'% done');
        });

        $this->line('northstar:default-sms-topics - Added "general" and "voting" to each appropriate user.');
    }
}
