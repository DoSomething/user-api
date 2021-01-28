<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Console\Command;

class TagPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:tagposts {--tag=: The id of the tag to use.} {--posts=: Comma-separated list of the post_ids to update.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tag a set of posts with the given tag id';

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
        $postIds = explode(',', $this->option('posts'));
        $posts = Post::find($postIds);

        $tag = Tag::findOrFail($this->option('tag'));

        if ($posts) {
            foreach ($posts as $post) {
                $this->info(
                    'Tagging post_id: ' . $post->id . ' as ' . $tag->tag_slug,
                );
                $post->tag($tag->tag_name);
            }
        }
    }
}
