<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\CustomerIo;
use Illuminate\Console\Command;
use League\Csv\Reader;

class FixSmsStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:fix-sms-status {--dry-run} {input=php://stdin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unset sms_status for the given accounts.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(CustomerIo $customerIo)
    {
        info('northstar:fix-sms-status - Starting up!');

        $input = file_get_contents($this->argument('input'));
        $csv = Reader::createFromString($input);
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $user = User::find($record['id']);

            if (!$user) {
                info('Could not find user', ['id' => $record['id']]);

                continue;
            }

            // If this user has a mobile (perhaps due to adding one after
            // our CSV was generated), skip unsetting their SMS status:
            if (!is_null($user->mobile)) {
                info('Skipping user', ['id' => $user->id]);

                continue;
            }

            $profile = $customerIo->getAttributes($user);

            throttle(600); // Customer.io Beta API is rate-limited, so keep under 10/s (600/min).

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

        info('northstar:fix-sms-status - All done!');
    }
}
