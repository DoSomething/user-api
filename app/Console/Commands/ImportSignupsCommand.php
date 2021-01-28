<?php

namespace App\Console\Commands;

use App\Jobs\SendSignupToCustomerIo;
use App\Models\Signup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use League\Csv\Reader;

class ImportSignupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:signupimport {path} {--logfreq=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create signups based on the data in the given (link to a) CSV file.';

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
        $logfreq = $this->option('logfreq');

        // Make a local copy of the CSV
        $path = $this->argument('path');
        info('rogue:signupimport: Loading in csv from ' . $path);

        $temp = tempnam(sys_get_temp_dir(), 'command_csv');
        file_put_contents($temp, fopen($this->argument('path'), 'r'));

        // Load the missing signups from the CSV
        $signups_csv = Reader::createFromPath($temp, 'r');
        $signups_csv->setHeaderOffset(0);
        $missing_signups = $signups_csv->getRecords();

        // Create each missing signup
        info('rogue:signupimport: Working on creating all the signups...');
        foreach ($missing_signups as $missing_signup) {
            // See if the signup exists
            $existing_signup = Signup::where([
                ['northstar_id', $missing_signup['northstar_id']],
                ['campaign_id', $missing_signup['campaign_id']],
            ])->first();

            // Create a signup if there isn't one already
            if (!$existing_signup) {
                $signup = Signup::create([
                    'northstar_id' => $missing_signup['northstar_id'],
                    'campaign_id' => $missing_signup['campaign_id'],
                    'source' => $missing_signup['source'],
                    'created_at' => $missing_signup[
                        'signup_created_at_timestamp'
                    ]
                        ? $missing_signup['signup_created_at_timestamp']
                        : Carbon::now(),
                ]);

                if ($signup->id % $logfreq == 0) {
                    info('rogue:signupimport: Created signup ' . $signup->id);
                }

                // Business Logic
                SendSignupToCustomerIo::dispatch($signup);
            } else {
                if ($existing_signup->id % $logfreq == 0) {
                    info(
                        'rogue:signupimport: Signup ' .
                            $existing_signup->id .
                            ' already exists! Moving on.',
                    );
                }
            }
        }

        // Tell everyone we're done!
        info('rogue:signupimport: ALL DONE!');
    }
}
