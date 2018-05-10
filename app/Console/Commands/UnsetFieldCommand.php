<?php

namespace Northstar\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class UnsetFieldCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:unset {field*}';

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
        $fieldsToRemove = $this->argument('field');
        /** @var \Jenssegers\Mongodb\Connection $connection */
        $connection = app('db')->connection('mongodb');

        foreach ($fieldsToRemove as $field) {
            $burnItDown = $this->confirm('Are you sure you want to remove this field from ALL USERS? `'.$field.'`');

            if ($burnItDown) {
                info('Removing field from all users: '.$field);
                // Unset $field
                $connection->collection('users')
                    ->whereRaw([$field => ['$exists' => true]])
                    ->unset([$field]);
                info('Field removed: '.$field);
            }
        }
    }
}
