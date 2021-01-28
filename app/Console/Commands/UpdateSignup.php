<?php

namespace App\Console\Commands;

use App\Managers\SignupManager;
use App\Models\Signup;
use Illuminate\Console\Command;

class UpdateSignup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:updatesignups {--target= : The name of the field to update} {--targetValue= : The value to update the target with} {--campaign= : The campaign_id to search for signups under} {--date= : Will be used to search for signups greater than the provided value}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update a target field on a set of signups constrained by the provided parameters';

    /**
     * The signup manager instance.
     *
     * @var App\Managers\SignupManager
     */
    protected $signups;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SignupManager $signups)
    {
        parent::__construct();

        $this->signups = $signups;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $targetField = $this->option('target') ?? null;
        $targetValue = $this->option('targetValue') ?? null;

        if (!$targetField) {
            $this->error('No target field specified.');

            return;
        }

        if (!$targetValue) {
            $this->error('No target value specified.');

            return;
        }

        $query = (new Signup())->newQuery();

        if ($this->option('campaign')) {
            $query = $query->where('campaign_id', $this->option('campaign'));
        }

        // Only take in one date and we assume to be looking for things on or after that date.
        // @TODO - Allow this to be more flexible (i.e accept two bounding dates for date ranges)
        if ($this->option('date')) {
            $query = $query->where('created_at', '>=', $this->option('date'));
        }

        $signups = $query->get();

        if ($signups->isNotEmpty()) {
            foreach ($signups as $signup) {
                $this->signups->update($signup, [$targetField => $targetValue]);
            }
        } else {
            $this->error('No signups found with that criteria.');

            return;
        }
    }
}
