<?php

namespace App\Console\Commands;

use App\Models\Post;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PostCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:postcleanup {date=2017-12-11}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate posts caused by blink erroring out and resending post requests';

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
        // Date of bug
        $date = $this->argument('date');

        // Grab all the posts we received on the given date and
        // group by the submissions having more than 1 of the same caption.
        $postsByCaption = Post::select(
            DB::raw('signup_id, caption, COUNT(caption) as caption_count'),
        )
            ->whereDate('created_at', $date)
            ->groupBy(['caption', 'signup_id'])
            ->having('caption_count', '>', 1)
            ->get();

        $this->info(
            'There are ' .
                $postsByCaption->count() .
                ' posts with more than 1 of the same caption',
        );

        // Loop through all the posts that have the
        // same caption per signup and delete all but the most recent one.
        foreach ($postsByCaption as $postCaption) {
            $this->info(
                'Getting posts under signup ' . $postCaption->signup_id,
            );

            $userPosts = Post::where('signup_id', $postCaption->signup_id)
                ->where('caption', $postCaption->caption)
                ->whereDate('created_at', $date)
                ->orderBy('created_at', 'asc')
                ->get();

            $mostRecent = $userPosts->pop();

            foreach ($userPosts as $post) {
                if ($mostRecent->id !== $post->id) {
                    $this->info('Deleting post ' . $post->id);

                    Storage::delete($post->url);

                    // Force delete so we really get rid of extraneous records.
                    $post->forceDelete();

                    $this->info('post ' . $post->id . ' deleted!');
                }
            }
        }

        $this->info('Cleanup Complete!');
    }
}
