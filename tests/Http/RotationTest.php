<?php

namespace Tests\Http;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;

class RotationTest extends TestCase
{
    /**
     * Test that an admin can view originals.
     *
     * @return void
     */
    public function testRotateImage()
    {
        $post = factory(Post::class)
            ->states('photo')
            ->create();

        $response = $this->asAdminUser()->postJson(
            '/api/v3/posts/' . $post->id . '/rotate',
            ['degrees' => 90],
        );

        $response->assertSuccessful();

        $this->fastlyMock->shouldHaveReceived('purge');
    }

    /**
     * Test that normal users can't view others' originals.
     *
     * @return void
     */
    public function testRotateImageAsNonOwner()
    {
        $somebodyElse = factory(User::class)->create();

        $post = factory(Post::class)
            ->states('photo')
            ->create();

        $response = $this->asUser($somebodyElse)->postJson(
            '/api/v3/posts/' . $post->id . '/rotate',
            ['degrees' => 90],
        );

        $response->assertUnauthorized();
    }

    /**
     * Test that normal users can't view others' originals.
     *
     * @return void
     */
    public function testRotateImageAsAnonymousUser()
    {
        $post = factory(Post::class)
            ->states('photo')
            ->create();

        $response = $this->postJson('/api/v3/posts/' . $post->id . '/rotate', [
            'degrees' => 90,
        ]);

        $response->assertUnauthorized();
    }
}
