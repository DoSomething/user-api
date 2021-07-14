<?php

namespace Tests\Http\Web;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class OriginalsTest extends TestCase
{
    /**
     * Test that an admin can view originals.
     *
     * @return void
     */
    public function testGetOriginalImage()
    {
        $admin = factory(User::class)->states('admin')->create();

        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        $response = $this->actingAs($admin, 'web')->get(
            'originals/' . $post->id,
        );

        $response->assertSuccessful();
    }

    /**
     * Test that normal users can't view others' originals.
     *
     * @return void
     */
    public function testGetOriginalImageAsNonOwner()
    {
        $somebodyElse = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('photo', 'accepted')
            ->create();

        $response = $this->actingAs($somebodyElse, 'web')->get(
            'originals/' . $post->id,
        );

        $response->assertStatus(302); // TODO: This should show an error page rather than double-redirect.
    }
}
