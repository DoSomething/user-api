<?php

namespace App\Jobs;

use App\Jobs\Middleware\CustomerIoRateLimit;
use App\Models\Post;
use App\Services\CustomerIo;

class SendPostToCustomerIo extends Job
{
    /**
     * The post to send to Customer.io.
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
     * Get the middleware the job should pass through.
     *
     * @return array
     */
    public function middleware()
    {
        return [new CustomerIoRateLimit()];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(CustomerIo $customerIo)
    {
        $this->post->user->trackCustomerIoEvent(
            'campaign_signup_post',
            $this->post->toCustomerIoPayload(),
        );
    }
}
