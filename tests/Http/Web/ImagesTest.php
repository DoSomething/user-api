<?php

namespace Tests\Http\Web;

use App\Models\Post;
use Tests\TestCase;

class ImagesTest extends TestCase
{
    /**
     * Test for endpoint throttling.
     *
     * @return void
     */
    public function testImagesThrottle()
    {
        $posts = factory(Post::class, 3)
            ->states('photo', 'accepted')
            ->create();

        for ($i = 0; $i < 60; $i++) {
            $response = $this->getJson('images/' . $posts->random()->hash);
            $response->assertStatus(200);
        }

        // Get a "Too Many Attempts" response when asking to render a 61st image. Since
        // rendered images are cached in Fastly, this should be unlikely for real users!
        $response = $this->getJson('images/' . $posts->random()->id);
        $response->assertStatus(429);
    }

    /**
     * Test that we allow cross-origin requests for images.
     *
     * @return void
     */
    public function testCorsWhitelist()
    {
        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        $response = $this->getJson('images/' . $post->hash, [
            'Origin' => 'https://www.dosomething.org',
        ]);

        $response->assertHeader(
            'Access-Control-Allow-Origin',
            'https://www.dosomething.org',
        );
    }
}
