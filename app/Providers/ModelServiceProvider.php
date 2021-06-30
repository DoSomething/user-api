<?php

namespace App\Providers;

use App\Jobs\RejectPost;
use App\Models\Event;
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
            if (
                in_array($post->text, [
                    'Test runscope upload',
                    'caption_ghost_test',
                ]) ||
                in_array($post->signup->why_participated, [
                    'why_participated_ghost_test',
                ])
            ) {
                // The post will delay for 2 minutes before being rejected to assure tests are running normally
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
