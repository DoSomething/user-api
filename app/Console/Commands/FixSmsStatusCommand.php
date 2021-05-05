<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CustomerIo;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixSmsStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:fix-sms-status {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix accounts with active sms_status but no mobile number.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(CustomerIo $customerIo)
    {
        info('northstar:fix-sms-status - Starting up!');

        // Get addressable users with a null mobile:
        $query = User::whereIn('sms_status', [
            'active',
            'pending',
            'less',
        ])->where('mobile', 'exists', false);

        $query->chunkById(200, function (Collection $users) use ($customerIo) {
            foreach ($users as $user) {
                $profile = $customerIo->getAttributes($user);

                // We want to log a little context on each user that we're fixing
                // to help identify patterns of potentially affected accounts:
                info('Fixing user', [
                    'id' => $user->id,
                    'has_profile' => !empty($profile),
                    'phone' => data_get($profile, 'phone'),
                    'source' => $user->source,
                    'created_at' => $user->created_at,
                ]);

                if ($this->option('dry-run')) {
                    continue;
                }

                $user->unset('sms_status');
            }
        });

        info('northstar:fix-sms-status - All done!');
    }
}
