<?php

namespace App\Console\Commands;

use App\Managers\PostManager;
use App\Managers\SignupManager;
use App\Models\Post;
use App\Models\Signup;
use Illuminate\Console\Command;

class UpdateSignupAndOrPostField extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:updatefield {target} {targetOldValue} {targetNewValue} {--signups} {--posts} {--logfreq=10000} {--log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change all target old values to target new value in the signups and/or posts tables';

    /**
     * The Signup Manager instance.
     *
     * @var App\Managers\SignupManager
     */
    protected $signups;

    /**
     * The Post Manager instance.
     *
     * @var App\Managers\PostManager
     */
    protected $posts;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SignupManager $signups, PostManager $posts)
    {
        parent::__construct();

        $this->signups = $signups;
        $this->posts = $posts;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        info('rogue:updatefield: Starting script!');

        $targetField = $this->argument('target');
        $targetOldValue =
            $this->argument('targetOldValue') !== 'NULL'
                ? $this->argument('targetOldValue')
                : null;
        $targetNewValue =
            $this->argument('targetNewValue') !== 'NULL'
                ? $this->argument('targetNewValue')
                : null;
        $signups = $this->option('signups');
        $posts = $this->option('posts');
        $logfreq = $this->option('logfreq');
        $log = $this->option('log');

        if ($signups) {
            // Start updating signups
            info('rogue:updatefield: Starting to update signups!');

            // Get all signups that have "targetOldValue" set as their target and update to "targetNewValue"
            Signup::withTrashed()
                ->where($targetField, $targetOldValue)
                ->chunkById(100, function ($signups) use (
                    $targetField,
                    $targetNewValue,
                    $logfreq,
                    $log
                ) {
                    foreach ($signups as $signup) {
                        if ($signup->id % $logfreq == 0) {
                            info(
                                'rogue:updatefield: changing ' .
                                    $targetField .
                                    ' to ' .
                                    $targetNewValue .
                                    ' for signup ' .
                                    $signup->id,
                            );
                        }

                        // Only log that signup was sent to Quasar if $log is TRUE in interest of space in Papertrail.
                        $this->signups->update(
                            $signup,
                            [$targetField => $targetNewValue],
                            $log,
                        );
                    }
                });

            // Log that updating signups are finished
            info('rogue:updatefield: Finished updating signups!');
        }

        if ($posts) {
            // Start updating posts
            info('rogue:updatefield: Starting to update posts!');

            // Get all posts that have "targetOldValue" set as their target and update to "targetNewValue"
            Post::withTrashed()
                ->where($targetField, $targetOldValue)
                ->chunkById(100, function ($posts) use (
                    $targetField,
                    $targetNewValue,
                    $logfreq,
                    $log
                ) {
                    foreach ($posts as $post) {
                        if ($post->id % $logfreq == 0) {
                            info(
                                'rogue:updatefield: changing ' .
                                    $targetField .
                                    ' to ' .
                                    $targetNewValue .
                                    ' for post ' .
                                    $post->id,
                            );
                        }

                        // Only log that signup was sent to Quasar if $log is TRUE in interest of space in Papertrail.
                        $this->posts->update(
                            $post,
                            [$targetField => $targetNewValue],
                            $log,
                        );
                    }
                });

            // Log that updating posts are finished
            info('rogue:updatefield: Finished updating posts!');
        }

        info('rogue:updatefield: ALL DONE!');
    }
}
