<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class BackfillHoursSpentOnPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:backfill-hours-spent-on-posts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfills informally stored "hours" field in Post details to the new hours_spent column.';

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
        info(
            'rogue:backfill-hours-spent-on-posts: Backfilling posts with "hours" field in "details" to "hours_spent" field',
        );

        $posts = Post::where('details', 'like', '%hours%')->get();

        info(
            'rogue:backfill-hours-spent-on-posts: There are about ' .
                $posts->count() .
                ' posts to update.',
        );

        $numUpdated = 0;

        foreach ($posts as $post) {
            $details = json_decode($post->details);
            $hours = data_get($details, 'hours');

            if ($hours && !$post->hours_spent) {
                $post->update(['hours_spent' => (float) $hours]);

                $numUpdated++;

                info(
                    'rogue:backfill-hours-spent-on-posts: Backfilling post_id: ' .
                        $post->id .
                        ' with hours_spent: ' .
                        $hours,
                );
            }
        }

        info(
            'rogue:backfill-hours-spent-on-posts: Backfill completed! ' .
                $numUpdated .
                ' posts updated.',
        );
    }
}
