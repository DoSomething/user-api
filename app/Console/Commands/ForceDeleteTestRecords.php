<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Signup;
use Illuminate\Console\Command;

class ForceDeleteTestRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rogue:forceDeleteTestRecords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force deletes signups and posts made in Runscope and Ghost Inspector tests';

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
        // Force delete all signups created by Runscope, identified by source.
        Signup::where('source', 'runscope')
            ->orWhere('source', 'runscope-oauth')
            ->forceDelete();

        // Force delete all posts created by Runscope, identified by source.
        Post::where('source', 'runscope')
            ->orWhere('source', 'runscope-oauth')
            ->forceDelete();

        $this->info('All Runscope signups and posts deleted.');

        // Force delete all signups created by Ghost Inspector tests, identified by 'why_participated.'
        Signup::where(
            'why_participated',
            'why_participated_ghost_test',
        )->forceDelete();

        // Force delete all posts created by Ghost Inspector tests, identified by caption.
        Post::where('text', 'caption_ghost_test')->forceDelete();

        $this->info('All Ghost Inspector signups and posts deleted.');
    }
}
