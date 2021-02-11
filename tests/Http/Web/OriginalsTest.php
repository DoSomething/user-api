<?php

use App\Models\Post;
use App\Models\User;

class OriginalsTest extends TestCase
{
    /**
     * Test that an admin can view originals.
     *
     * @return void
     */
    public function testGetOriginalImage()
    {
        $admin = factory(User::class, 'admin')->create();

        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        $this->be($admin, 'web');

        $response = $this->get('originals/' . $post->id);
        $response->assertSuccessful();
    }

    /**
     * Test that normal users can't view others' originals.
     *
     * @return void
     */
    public function test()
    {
        $somebodyElse = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        $this->be($somebodyElse, 'web');

        $response = $this->get('originals/' . $post->id);
        $response->assertStatus(302); // Redirect to 'register'.
    }
}
