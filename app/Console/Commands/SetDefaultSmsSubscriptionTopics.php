<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SetDefaultSmsSubscriptionTopics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:default-sms-topics {smsStatus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add "general" and "voting" SMS topics for each user who does not have topics set and has the given SMS status.';

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
        $smsStatus = $this->argument('smsStatus');

        if (!in_array($smsStatus, ['active', 'less', 'pending'])) {
            $this->line(
                'northstar:default-sms-topics - Invalid smsStatus argument "' .
                    $smsStatus .
                    '".',
            );

            return;
        }

        // Use low priority queue for these updates
        config(['queue.jobs.users' => 'low']);

        // Grab users who have our status argument and do not have topics set.
        $query = (new User())->newQuery();
        $query = $query->where('sms_status', $smsStatus);
        $query = $query->where('sms_subscription_topics', null);

        $totalCount = $query->count();

        if ($totalCount === 0) {
            $this->line(
                'northstar:default-sms-topics - No users with smsStatus "' .
                    $smsStatus .
                    '" need updating.',
            );

            return;
        }

        $query->chunkById(200, function (Collection $users) use (
            $totalCount,
            $smsStatus
        ) {
            $users->each(function (User $user) {
                $user->sms_subscription_topics = ['general', 'voting'];
                $user->save();
            });

            // Logging to track progress (may read over 100% at the end when there are less than 200 users in the last chunk)
            $this->currentCount += 200;
            $percentDone = ($this->currentCount / $totalCount) * 100;
            $this->line(
                'northstar:default-sms-topics - smsStatus "' .
                    $smsStatus .
                    '" - ' .
                    $this->currentCount .
                    '/' .
                    $totalCount .
                    ' - ' .
                    $percentDone .
                    '% done',
            );
        });

        $this->line(
            'northstar:default-sms-topics - Added "general" and "voting" to each appropriate user with smsStatus "' .
                $smsStatus .
                '".',
        );
    }
}
