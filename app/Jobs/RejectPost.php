<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Models\Post;

class RejectPost extends Job
{
    /**
     * The post to reject.
     *
     * @var Post
     */
    protected $post;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Post $post)
    {
        $this->post = $post;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->post->status = 'rejected';
        $this->post->save();

        info('Automatically rejected post ' . $this->post->id);
    }
}
