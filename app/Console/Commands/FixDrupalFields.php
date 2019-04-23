<?php

namespace Northstar\Console\Commands;

use Northstar\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class FixDrupalFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'northstar:dedrupal {field}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update fields that have nasty Drupal objects if possible.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $field = $this->argument('field');

        // Get users where field is not a string:
        $query = User::whereRaw([
            $field => [
                '$exists' => true,
                '$not' => ['$type' => 'string'],
            ],
        ]);

        $progressBar = $this->output->createProgressBar($query->count());
        $query->chunkById(200, function (Collection $users) use ($field, $progressBar) {
            foreach ($users as $user) {
                $value = $user->{$field};

                // If this is an object, it's likely Drupal gook.
                if (is_array($value)) {
                    $sanitized = array_get($value, 'value');
                    $user->{$field} = $sanitized;
                } else {
                    $user->{$field} = null;
                }

                $progressBar->advance();
                $user->save();

                info('Sanitized object field.', [
                    'id' => $user->id,
                    'field' => $field,
                    'before' => $value,
                    'after' => $user->{$field},
                ]);
            }
        });

        $progressBar->finish();
    }
}
