<?php

namespace App\Providers;

use App\Jobs\RejectPost;
use App\Models\Post;
use App\Models\Review;
use App\Models\Signup;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Post::created(function ($post) {
            if ($post->isAutomatedTest()) {
                // Wait a bit to allow tests to assert against normal behavior, then
                // automatically reject this post so it doesn't bother reviewers:
                RejectPost::dispatch($post)->delay(now()->addMinutes(2));
            }
        });

        Post::saved(function ($post) {
            // If this post has a signup, update its quantity:
            if ($post->signup) {
                $post->signup->refreshQuantity();
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
