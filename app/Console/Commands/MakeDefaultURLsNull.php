<?php

namespace App\Console\Commands;

use App\Models\Post;
use Illuminate\Console\Command;

class MakeDefaultURLsNull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:nullurl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change "default" URLs in the Posts table to null';

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
        info('rogue:nullurl: Starting!');

        // All posts that have "default" set as their url
        $query = Post::where('url', 'default');

        // Create the progress bar
        $bar = $this->output->createProgressBar($query->count());

        // Update all the posts
        $query->chunkById(100, function ($posts) use ($bar) {
            // Set all of the "default"s to null
            foreach ($posts as $post) {
                info('rogue:nullurl: Nulling url for post ' . $post->id);
                $post->url = null;
                $post->save();
                $bar->advance();
            }
        });

        // Finish the progress bar
        $bar->finish();
        info('rogue:nullurl: All done!');
    }
}
