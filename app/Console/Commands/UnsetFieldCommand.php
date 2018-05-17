<?php

namespace Northstar\Console\Commands;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UnsetFieldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:unset {field*} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unset the given field from all users.';

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
        $force = $this->option('force');

        if ($force || $this->confirm('Have you made sure that there is NOT a broadcast in progress or starting soon?')) {
            $fieldsToRemove = $this->argument('field');

            foreach ($fieldsToRemove as $field) {
                $burnItDown = false;

                if (! $force) {
                    $burnItDown = $this->confirm('Are you sure you want to remove this field from ALL USERS? `'.$field.'`');
                }

                if ($burnItDown || $this->option('force')) {
                    info('Removing field from all users: '.$field);

                    $usersToUnset = DB::collection('users')
                                    ->whereRaw([$field => ['$exists' => true]])
                                    ->update(['$unset' => [$field => '']], ['maxTimeMS' => -1]);

                    $usersLeft = DB::collection('users')->whereRaw([$field => ['$exists' => true]])->count();

                    if (! $usersLeft) {
                        $this->line('Field removed from all users: '.$field);
                    } else {
                        $this->line('Oops! '.$usersLeft.' users still have field: '.$field);
                    }
                } else {
                    $this->line('Did NOT remove '.$field);
                }
            }
        }
    }
}
