<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
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

        foreach ($fieldsToRemove as $field) {
            $burnItDown = $this->confirm('Are you sure you want to remove this field from ALL USERS? `'.$field.'`');

            if ($burnItDown) {
                info('Removing field from all users: '.$field);

                $usersToUnset = (new User)->newQuery();
                $usersToUnset->whereRaw([$field => ['$exists' => true]]);

                $this->line('Progess removing '.$field.':');
                $progressBar = $this->output->createProgressBar($usersToUnset->count());

                $usersToUnset->chunkById(200, function (Collection $users) use ($progressBar, $field) {
                    $users->each(function (User $user) use ($progressBar, $field) {
                        $user->unset([$field]);
                        $progressBar->advance();
                    });
                });

                $progressBar->finish();
                info('Field removed: '.$field);
            }
        }
    }
}
