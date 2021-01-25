<?php

namespace App\Console\Commands;

use App\Managers\PostManager;
use App\Models\Post;
use Illuminate\Console\Command;

class DeletePosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:deleteposts
                            {ids* : A space-separated list of post ids to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete posts in Rogue by post id with an option to force delete';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(PostManager $postManager)
    {
        // Post Manager Instance.
        $this->postManager = $postManager;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = $this->argument('ids');

        $posts = Post::whereIn('id', $ids)->get();

        if ($posts->isNotEmpty()) {
            $posts->map(function ($post, $key) {
                $this->postManager->destroy($post->id);
            });

            $this->info('Posts Deleted!');
        } else {
            $this->info('No Posts found');
        }
    }
}
